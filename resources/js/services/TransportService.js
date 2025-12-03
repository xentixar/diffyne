/**
 * TransportService.js
 * Handles communication with server (Single Responsibility)
 * Supports AJAX and WebSocket transports
 */

import { getCsrfToken, generateId, getQueryParams, Logger } from '../utils/helpers.js';

export class TransportService {
    constructor(config, logger) {
        this.config = config;
        this.logger = logger;
        this.ws = null;
    }

    /**
     * Send request to server
     */
    async send(payload) {
        if (this.config.transport === 'websocket') {
            return this.sendWebSocket(payload);
        }
        return this.sendAjax(payload);
    }

    /**
     * Send AJAX request
     */
    async sendAjax(payload) {
        const response = await fetch(this.config.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok || !data.s) {
            const error = new Error(data.error || `HTTP ${response.status}: ${response.statusText}`);
            error.type = data.type;
            error.details = data;
            throw error;
        }

        return data;
    }

    /**
     * Send WebSocket message
     */
    sendWebSocket(payload) {
        return new Promise((resolve, reject) => {
            if (!this.ws || this.ws.readyState !== WebSocket.OPEN) {
                reject(new Error('WebSocket not connected'));
                return;
            }

            const requestId = generateId();
            
            let event = 'diffyne.call';
            if (payload.type === 'update') {
                event = 'diffyne.update';
            } else if (payload.type === 'call') {
                event = 'diffyne.call';
            }
            
            const message = {
                event: event,
                data: {
                    ...payload,
                    requestId: requestId
                }
            };

            const handler = (event) => {
                try {
                    const response = JSON.parse(event.data);
                    
                    if (response.event === 'diffyne.response' && 
                        response.data?.requestId === requestId) {
                        this.ws.removeEventListener('message', handler);
                        
                        const data = response.data;
                        
                        if (!data.s) {
                            const error = new Error(data.error || 'WebSocket request failed');
                            error.type = data.type;
                            error.details = data;
                            reject(error);
                        } else {
                            resolve(data);
                        }
                    }
                } catch (e) {
                    // Ignore parse errors for other messages
                }
            };

            this.ws.addEventListener('message', handler);
            this.ws.send(JSON.stringify(message));

            setTimeout(() => {
                this.ws.removeEventListener('message', handler);
                reject(new Error('Request timeout'));
            }, 30000);
        });
    }

    /**
     * Connect to WebSocket server
     */
    connectWebSocket(onOpen, onClose, onError) {
        const wsUrl = this.config.wsUrl || this.buildWebSocketUrl();
        this.ws = new WebSocket(wsUrl);

        this.ws.onopen = () => {
            if (onOpen) onOpen();
        };

        this.ws.onmessage = (event) => {
            try {
                const message = JSON.parse(event.data);

                if (message.event === 'diffyne.connected') {
                    this.logger.log('[Diffyne WS] Connected:', message.data);
                }
                
                if (message.event === 'diffyne.pong') {
                    this.logger.log('[Diffyne WS] Pong received');
                }
            } catch (e) {
                // Messages handled by request handlers
            }
        };

        this.ws.onclose = () => {
            if (onClose) onClose();
            setTimeout(() => this.connectWebSocket(onOpen, onClose, onError), 3000);
        };

        this.ws.onerror = (error) => {
            if (onError) onError(error);
        };
    }

    /**
     * Build WebSocket URL from config
     */
    buildWebSocketUrl() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const host = window.location.hostname;
        const port = this.config.wsPort || 6001;
        return `${protocol}//${host}:${port}`;
    }

    /**
     * Load lazy component
     */
    async loadLazy(componentClass, componentId, params, queryParams) {
        const response = await fetch(`${this.config.endpoint}/lazy`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                componentClass,
                componentId,
                params,
                queryParams,
            }),
        });

        return response.json();
    }
}
