/**
 * ComponentRegistry.js
 * Manages component instances (Single Responsibility)
 */

import { Component } from './Component.js';

export class ComponentRegistry {
    constructor() {
        this.components = new Map();
    }

    /**
     * Register a component
     */
    register(id, data) {
        const component = new Component(data);
        this.components.set(id, component);
        return component;
    }

    /**
     * Get component by ID
     */
    get(id) {
        return this.components.get(id);
    }

    /**
     * Check if component exists
     */
    has(id) {
        return this.components.has(id);
    }

    /**
     * Remove component
     */
    remove(id) {
        return this.components.delete(id);
    }

    /**
     * Get all components
     */
    getAll() {
        return Array.from(this.components.values());
    }

    /**
     * Clear all components
     */
    clear() {
        this.components.clear();
    }
}
