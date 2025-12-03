/**
 * LoadingService.js
 * Manages loading states for components (Single Responsibility)
 */

export class LoadingService {
    /**
     * Show loading state
     */
    show(element) {
        const loadingElements = this.findLoadingElements(element);
        
        loadingElements.forEach(el => {
            const loadingAttr = this.findLoadingAttribute(el);
            if (!loadingAttr) return;
            
            const parts = loadingAttr.name.split('.');
            
            if (parts.includes('class')) {
                this.showLoadingClass(el, parts);
            } else if (parts.includes('remove')) {
                this.showLoadingRemove(el, parts);
            } else if (parts.includes('attr')) {
                this.showLoadingAttr(el, parts);
            } else {
                this.showLoadingDefault(el);
            }
        });
    }

    /**
     * Hide loading state
     */
    hide(element) {
        const loadingElements = this.findLoadingElements(element);
        
        loadingElements.forEach(el => {
            const loadingAttr = this.findLoadingAttribute(el);
            if (!loadingAttr) return;
            
            const parts = loadingAttr.name.split('.');
            
            if (parts.includes('class')) {
                this.hideLoadingClass(el, parts);
            } else if (parts.includes('remove')) {
                this.hideLoadingRemove(el, parts);
            } else if (parts.includes('attr')) {
                this.hideLoadingAttr(el, parts);
            } else {
                this.hideLoadingDefault(el);
            }
        });
    }

    /**
     * Find elements with loading directive
     */
    findLoadingElements(element) {
        return Array.from(element.querySelectorAll('*')).filter(el => {
            return Array.from(el.attributes).some(attr => 
                attr.name.startsWith('diff:loading')
            );
        });
    }

    /**
     * Find loading attribute
     */
    findLoadingAttribute(element) {
        return Array.from(element.attributes).find(attr => 
            attr.name.startsWith('diff:loading')
        );
    }

    /**
     * Show loading with class modifier
     */
    showLoadingClass(el, parts) {
        const classIndex = parts.indexOf('class');
        if (parts[classIndex + 1]) {
            el.classList.add(parts[classIndex + 1]);
        }
    }

    /**
     * Hide loading with class modifier
     */
    hideLoadingClass(el, parts) {
        const classIndex = parts.indexOf('class');
        if (parts[classIndex + 1]) {
            el.classList.remove(parts[classIndex + 1]);
        }
    }

    /**
     * Show loading with remove modifier
     */
    showLoadingRemove(el, parts) {
        const removeIndex = parts.indexOf('remove');
        if (parts[removeIndex + 1]) {
            el.classList.remove(parts[removeIndex + 1]);
        }
    }

    /**
     * Hide loading with remove modifier
     */
    hideLoadingRemove(el, parts) {
        const removeIndex = parts.indexOf('remove');
        if (parts[removeIndex + 1]) {
            el.classList.add(parts[removeIndex + 1]);
        }
    }

    /**
     * Show loading with attr modifier
     */
    showLoadingAttr(el, parts) {
        const attrIndex = parts.indexOf('attr');
        if (parts[attrIndex + 1]) {
            const attrName = parts[attrIndex + 1];
            const attrValue = parts[attrIndex + 2] || '';
            
            const originalValue = el.getAttribute(attrName);
            if (originalValue !== null) {
                el.setAttribute('data-diffyne-loading-original-' + attrName, originalValue);
            }
            
            el.setAttribute(attrName, attrValue);
        }
    }

    /**
     * Hide loading with attr modifier
     */
    hideLoadingAttr(el, parts) {
        const attrIndex = parts.indexOf('attr');
        if (parts[attrIndex + 1]) {
            const attrName = parts[attrIndex + 1];
            const originalAttr = 'data-diffyne-loading-original-' + attrName;
            
            if (el.hasAttribute(originalAttr)) {
                const originalValue = el.getAttribute(originalAttr);
                el.setAttribute(attrName, originalValue);
                el.removeAttribute(originalAttr);
            } else {
                el.removeAttribute(attrName);
            }
        }
    }

    /**
     * Show default loading state
     */
    showLoadingDefault(el) {
        el.style.opacity = '0.5';
        el.style.pointerEvents = 'none';
    }

    /**
     * Hide default loading state
     */
    hideLoadingDefault(el) {
        el.style.opacity = '';
        el.style.pointerEvents = '';
    }
}
