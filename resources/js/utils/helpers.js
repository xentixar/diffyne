/**
 * helpers.js
 * Utility functions (DRY principle)
 */

/**
 * Parse JSON safely
 */
export function parseJSON(str) {
    try {
        return JSON.parse(str);
    } catch {
        return {};
    }
}

/**
 * Parse action string
 */
export function parseAction(action) {
    const match = action.match(/^(\w+)(?:\((.*)\))?$/);
    
    if (!match) return [action];

    const method = match[1];
    const paramsStr = match[2];

    if (!paramsStr) return [method];

    const params = paramsStr.split(',').map(p => {
        p = p.trim();
        if (p.startsWith("'") || p.startsWith('"')) {
            return p.slice(1, -1);
        }
        if (!isNaN(p)) return Number(p);
        if (p === 'true') return true;
        if (p === 'false') return false;
        return p;
    });

    return [method, ...params];
}

/**
 * Update URL query string
 */
export function updateQueryString(params) {
    const url = new URL(window.location);
    
    Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== '' && params[key] !== undefined) {
            url.searchParams.set(key, params[key]);
        } else {
            url.searchParams.delete(key);
        }
    });
    
    window.history.pushState({}, '', url);
}

/**
 * Extract URL query parameters
 */
export function getQueryParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const params = {};
    for (const [key, value] of urlParams) {
        params[key] = value;
    }
    return params;
}

/**
 * Get CSRF token from meta tag
 */
export function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

/**
 * Generate random ID
 */
export function generateId() {
    return 'req-' + Math.random().toString(36).substr(2, 9);
}

/**
 * Debounce function
 */
export function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

/**
 * Logger utility
 */
export class Logger {
    constructor(debug = false) {
        this.debug = debug;
    }

    log(...args) {
        if (this.debug) {
            console.log('[Diffyne]', ...args);
        }
    }

    error(...args) {
        console.error('[Diffyne Error]', ...args);
    }
}
