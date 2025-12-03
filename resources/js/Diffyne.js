/**
 * Diffyne.js
 * Main entry point - orchestrates all modules (Dependency Inversion Principle)
 */

import { ComponentRegistry } from './core/ComponentRegistry.js';
import { EventBinder } from './core/EventBinder.js';
import { PatchApplier } from './core/PatchApplier.js';
import { VNodeConverter } from './core/VNodeConverter.js';
import { TransportService } from './services/TransportService.js';
import { LoadingService } from './services/LoadingService.js';
import { ErrorService } from './services/ErrorService.js';
import { ModelSyncService } from './services/ModelSyncService.js';
import { parseJSON, parseAction, updateQueryString, getQueryParams, Logger } from './utils/helpers.js';

export class Diffyne {
    constructor(config = {}) {
        this.config = {
            transport: config.transport || 'ajax',
            wsUrl: config.wsUrl || 'ws://localhost:8080/diffyne',
            endpoint: config.endpoint || '/_diffyne',
            debug: config.debug || false,
            ...config
        };

        // Initialize services (Dependency Injection)
        this.registry = new ComponentRegistry();
        this.transport = new TransportService(this.config);
        this.patchApplier = new PatchApplier();
        this.vNodeConverter = new VNodeConverter();
        this.loadingService = new LoadingService();
        this.errorService = new ErrorService();
        this.modelSync = new ModelSyncService();
        this.logger = new Logger(this.config.debug);
        
        // Initialize event binder with handlers
        this.eventBinder = new EventBinder(
            (id, action, event) => this.handleAction(id, action, event),
            (id, property, value) => this.handleModelUpdate(id, property, value)
        );

        this.init();
    }

    /**
     * Initialize Diffyne
     */
    init() {
        this.logger.log('Initializing Diffyne...');

        if (this.config.transport === 'websocket') {
            this.transport.connectWebSocket(
                () => this.logger.log('WebSocket connected'),
                () => this.logger.log('WebSocket disconnected'),
                (error) => this.logger.error('WebSocket error:', error)
            );
        }

        this.hydrateComponents();
        setTimeout(() => this.loadLazyComponents(), 100);
        this.observeDOMChanges();

        window.addEventListener('popstate', () => {
            this.logger.log('Browser navigation detected, reloading page');
            window.location.reload();
        });
    }

    /**
     * Hydrate all components
     */
    hydrateComponents() {
        const elements = document.querySelectorAll('[diff\\:id]');
        
        elements.forEach(element => {
            if (element.hasAttribute('data-diffyne-lazy')) {
                return; // Skip lazy components
            }

            this.hydrateComponent(element);
        });
    }

    /**
     * Hydrate single component
     */
    hydrateComponent(element) {
        const id = element.getAttribute('diff:id');
        const componentClass = element.getAttribute('diff:class');
        const componentName = element.getAttribute('diff:name');
        const state = parseJSON(element.getAttribute('diff:state') || '{}');
        const fingerprint = element.getAttribute('diff:fingerprint');

        this.registry.register(id, {
            id,
            componentClass,
            componentName,
            element,
            state,
            fingerprint,
            vdom: this.vNodeConverter.buildVDOM(element),
        });

        this.modelSync.sync(element, state);
        this.eventBinder.bind(element, id);
        
        this.logger.log(`Hydrated component: ${id} (${componentName})`);
    }

    /**
     * Load all lazy components
     */
    loadLazyComponents() {
        const lazyElements = document.querySelectorAll('[data-diffyne-lazy]');
        lazyElements.forEach(element => this.loadLazyComponent(element));
    }

    /**
     * Load single lazy component
     */
    async loadLazyComponent(element) {
        const id = element.getAttribute('diff:id');
        const componentClass = element.getAttribute('diff:class');
        const componentName = element.getAttribute('diff:name');
        const params = parseJSON(element.getAttribute('diff:params') || '{}');
        const queryParams = getQueryParams();

        this.logger.log(`Loading lazy component: ${id} (${componentName})`);

        try {
            const data = await this.transport.loadLazy(
                componentClass, 
                id, 
                params, 
                queryParams
            );

            if (data.success) {
                element.setAttribute('diff:state', JSON.stringify(data.state));
                element.setAttribute('diff:fingerprint', data.fingerprint);
                element.removeAttribute('data-diffyne-lazy');
                element.setAttribute('data-diffyne-loaded', '');
                element.innerHTML = data.html;

                this.registry.register(id, {
                    id,
                    componentClass,
                    componentName,
                    element,
                    state: data.state,
                    fingerprint: data.fingerprint,
                    vdom: this.vNodeConverter.buildVDOM(element),
                });

                this.modelSync.sync(element, data.state);
                this.eventBinder.bind(element, id);

                this.logger.log(`Lazy component loaded: ${id} (${componentName})`);
            } else {
                element.innerHTML = `<div style="color: red; padding: 1rem;">Failed to load component: ${data.error}</div>`;
            }
        } catch (error) {
            this.logger.error('Error loading lazy component:', error);
            element.innerHTML = `<div style="color: red; padding: 1rem;">Error loading component</div>`;
        }
    }

    /**
     * Handle action (method call)
     */
    async handleAction(componentId, action, event = null) {
        const [method, ...args] = parseAction(action);
        this.logger.log(`Action: ${method}`, args);
        await this.callMethod(componentId, method, args);
    }

    /**
     * Handle model update
     */
    async handleModelUpdate(componentId, property, value) {
        this.logger.log(`Model update: ${property} = ${value}`);
        await this.updateProperty(componentId, property, value);
    }

    /**
     * Call component method
     */
    async callMethod(componentId, method, params = []) {
        const component = this.registry.get(componentId);
        if (!component) return;

        this.loadingService.show(component.element);

        try {
            const response = await this.transport.send({
                type: 'call',
                componentId,
                componentClass: component.componentClass,
                method,
                params,
                state: component.state,
                fingerprint: component.fingerprint
            });

            this.processResponse(componentId, response);
        } catch (error) {
            this.handleError(componentId, error);
        } finally {
            this.loadingService.hide(component.element);
        }
    }

    /**
     * Update component property
     */
    async updateProperty(componentId, property, value) {
        const component = this.registry.get(componentId);
        if (!component) return;

        // Optimistic update
        component.updateState({ [property]: value });

        try {
            const response = await this.transport.send({
                type: 'update',
                componentId,
                componentClass: component.componentClass,
                property,
                value,
                state: component.state,
                fingerprint: component.fingerprint
            });

            this.processResponse(componentId, response);
        } catch (error) {
            this.handleError(componentId, error);
        }
    }

    /**
     * Process server response
     */
    processResponse(componentId, response) {
        const component = this.registry.get(componentId);
        if (!component) return;

        const success = response.s !== undefined ? response.s : response.success;
        
        if (success && response.redirect) {
            window.location.href = response.redirect.url;
            return;
        }
        
        const componentData = response.c || response.component;
        if (!success || !componentData) return;

        const patches = componentData.p || componentData.patches || [];
        const state = componentData.st || componentData.state;
        const fingerprint = componentData.f || componentData.fingerprint;
        const errors = componentData.e || componentData.errors;
        const queryString = componentData.q || componentData.queryString;

        this.logger.log(`Applying ${patches.length} patches to ${componentId}`, patches);

        const contentRoot = component.element.firstElementChild;
        if (contentRoot) {
            this.patchApplier.applyPatches(contentRoot, patches);
        }

        if (state) {
            component.updateState(state);
            this.modelSync.sync(component.element, state);
        }
        
        if (fingerprint) {
            component.updateFingerprint(fingerprint);
        }

        if (queryString) {
            updateQueryString(queryString);
        }

        if (errors) {
            component.setErrors(errors);
            this.errorService.display(component.element, errors);
        } else {
            component.clearErrors();
            this.errorService.clear(component.element);
        }
    }

    /**
     * Handle errors
     */
    handleError(componentId, error) {
        const component = this.registry.get(componentId);
        
        if (error.type === 'validation_error' && error.details?.errors) {
            if (component) {
                component.setErrors(error.details.errors);
                this.errorService.display(component.element, error.details.errors);
            }
        } else {
            this.errorService.handle(error, this.config.debug);
        }
    }

    /**
     * Observe DOM changes
     */
    observeDOMChanges() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const newComponents = node.querySelectorAll('[diff\\:id]');
                        newComponents.forEach(el => this.hydrateComponent(el));
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
}

// Auto-initialize
if (typeof window !== 'undefined') {
    window.Diffyne = Diffyne;
    
    document.addEventListener('DOMContentLoaded', () => {
        const config = window.diffyneConfig || {};
        window.diffyne = new Diffyne(config);
    });
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = Diffyne;
}
