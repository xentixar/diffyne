/**
 * TransportService.js
 * Handles communication with server (Single Responsibility)
 * Supports AJAX and WebSocket transports
 */

export class TransportService {
    constructor(config) {
        this.config = config;
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
                'X-CSRF-TOKEN': this.getCsrfToken()
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

            const requestId = this.generateId();
            payload.requestId = requestId;

            const handler = (event) => {
                const response = JSON.parse(event.data);
                if (response.requestId === requestId) {
                    this.ws.removeEventListener('message', handler);
                    resolve(response);
                }
            };

            this.ws.addEventListener('message', handler);
            this.ws.send(JSON.stringify(payload));

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
        this.ws = new WebSocket(this.config.wsUrl);

        this.ws.onopen = () => {
            if (onOpen) onOpen();
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
     * Load lazy component
     */
    async loadLazy(componentClass, componentId, params, queryParams) {
        const response = await fetch(`${this.config.endpoint}/lazy`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
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

    /**
     * Get CSRF token
     */
    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    /**
     * Generate random ID
     */
    generateId() {
        return 'req-' + Math.random().toString(36).substr(2, 9);
    }
}
