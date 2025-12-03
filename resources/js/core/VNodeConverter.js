/**
 * VNodeConverter.js
 * Converts Virtual DOM nodes to actual DOM nodes (Single Responsibility)
 */

export class VNodeConverter {
    /**
     * Convert VNode to actual DOM node
     */
    vnodeToDOM(vnode) {
        // Text node (minified)
        if (vnode.x !== undefined) {
            return document.createTextNode(vnode.x);
        }
        
        // Comment node (minified)
        if (vnode.m !== undefined) {
            return document.createComment(vnode.m);
        }
        
        // Element node (minified)
        if (vnode.t) {
            const element = document.createElement(vnode.t);
            
            const attrs = vnode.a || vnode.attributes || {};
            Object.entries(attrs).forEach(([key, value]) => {
                element.setAttribute(key, value);
            });
            
            const children = vnode.c || vnode.children || [];
            children.forEach(child => {
                element.appendChild(this.vnodeToDOM(child));
            });
            
            return element;
        }
        
        // Legacy format fallback
        if (vnode.type === 'text') {
            return document.createTextNode(vnode.text);
        }

        if (vnode.type === 'element') {
            const element = document.createElement(vnode.tag);

            Object.entries(vnode.attributes || {}).forEach(([key, value]) => {
                element.setAttribute(key, value);
            });

            (vnode.children || []).forEach(child => {
                element.appendChild(this.vnodeToDOM(child));
            });

            return element;
        }

        return document.createTextNode('');
    }

    /**
     * Build simplified VDOM from actual DOM
     */
    buildVDOM(element) {
        return {
            tag: element.tagName.toLowerCase(),
            attrs: this.getAttributes(element),
            children: Array.from(element.childNodes).map(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    return { type: 'text', value: node.textContent };
                } else if (node.nodeType === Node.ELEMENT_NODE) {
                    return this.buildVDOM(node);
                }
                return null;
            }).filter(Boolean)
        };
    }

    /**
     * Get all attributes from an element
     */
    getAttributes(element) {
        const attrs = {};
        Array.from(element.attributes).forEach(attr => {
            attrs[attr.name] = attr.value;
        });
        return attrs;
    }
}
