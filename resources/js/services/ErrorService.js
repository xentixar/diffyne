/**
 * ErrorService.js
 * Handles error display and validation (Single Responsibility)
 */

export class ErrorService {
    /**
     * Display validation errors
     */
    display(element, errors) {
        this.clear(element);

        Object.entries(errors).forEach(([field, messages]) => {
            const fieldElement = element.querySelector(
                `[name="${field}"], [diff\\:model="${field}"]`
            );
            
            if (fieldElement) {
                this.markFieldAsInvalid(fieldElement);
                this.showErrorMessage(element, fieldElement, field, messages);
            }
        });
    }

    /**
     * Clear all errors
     */
    clear(element) {
        // Remove error classes
        element.querySelectorAll('.diffyne-error').forEach(el => {
            el.classList.remove('diffyne-error');
            el.removeAttribute('aria-invalid');
        });

        // Clear error displays
        element.querySelectorAll('[diff\\:error]').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });

        // Remove dynamic error messages
        element.querySelectorAll('.diffyne-error-message').forEach(el => {
            el.remove();
        });
    }

    /**
     * Mark field as invalid
     */
    markFieldAsInvalid(fieldElement) {
        fieldElement.classList.add('diffyne-error');
        fieldElement.setAttribute('aria-invalid', 'true');
    }

    /**
     * Show error message
     */
    showErrorMessage(element, fieldElement, field, messages) {
        const errorDisplay = element.querySelector(`[diff\\:error="${field}"]`);
        const message = Array.isArray(messages) ? messages[0] : messages;
        
        if (errorDisplay) {
            errorDisplay.textContent = message;
            errorDisplay.style.display = '';
        } else {
            this.createErrorElement(fieldElement, message);
        }
    }

    /**
     * Create dynamic error element
     */
    createErrorElement(fieldElement, message) {
        const errorEl = document.createElement('span');
        errorEl.className = 'diffyne-error-message';
        errorEl.textContent = message;
        errorEl.style.color = '#ef4444';
        errorEl.style.fontSize = '0.875rem';
        errorEl.style.marginTop = '0.25rem';
        
        if (fieldElement.parentNode) {
            fieldElement.parentNode.insertBefore(errorEl, fieldElement.nextSibling);
        }
    }

    /**
     * Handle error response
     */
    handle(error, debug = false) {
        console.error('[Diffyne Error]', error);
        
        if (error.type === 'validation_error') {
            return; // Handled inline
        }

        let message = 'An error occurred';
        
        if (error.type === 'method_error') {
            message = `Method Error: ${error.message}`;
        } else if (error.type === 'property_error') {
            message = `Property Error: ${error.message}`;
        } else if (error.type === 'exception' && error.details) {
            message = `${error.details.exception}: ${error.message}\nFile: ${error.details.file}:${error.details.line}`;
        } else {
            message = error.message || 'Unknown error occurred';
        }
        
        console.error('[Diffyne Error Details]', {
            type: error.type,
            message: error.message,
            details: error.details
        });
        
        if (debug) {
            alert(message);
        }
    }
}
