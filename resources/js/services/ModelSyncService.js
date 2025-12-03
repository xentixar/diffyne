/**
 * ModelSyncService.js
 * Syncs model-bound inputs with component state (Single Responsibility)
 */

export class ModelSyncService {
    /**
     * Sync all model inputs with state
     */
    sync(element, state) {
        const modelInputs = this.findModelInputs(element);
        
        modelInputs.forEach(input => {
            const modelAttr = this.findModelAttribute(input);
            if (!modelAttr) return;
            
            const property = input.getAttribute(modelAttr.name);
            const modifiers = this.parseModifiers(modelAttr.name, property);
            const propertyName = modifiers.property;
            
            if (state.hasOwnProperty(propertyName)) {
                this.syncInput(input, state[propertyName]);
            }
        });
    }

    /**
     * Find all model-bound inputs
     */
    findModelInputs(element) {
        return Array.from(element.querySelectorAll('*')).filter(el => {
            return Array.from(el.attributes).some(attr => 
                attr.name === 'diff:model' || attr.name.startsWith('diff:model.')
            );
        });
    }

    /**
     * Find model attribute
     */
    findModelAttribute(element) {
        return Array.from(element.attributes).find(attr => 
            attr.name === 'diff:model' || attr.name.startsWith('diff:model.')
        );
    }

    /**
     * Sync single input value
     */
    syncInput(input, value) {
        const normalizedValue = value ?? '';
        
        if (input.tagName === 'INPUT' || input.tagName === 'TEXTAREA') {
            if (input.value !== normalizedValue) {
                input.value = normalizedValue;
            }
        } else if (input.tagName === 'SELECT') {
            if (input.value !== normalizedValue) {
                input.value = normalizedValue;
            }
        }
    }

    /**
     * Parse modifiers from directive
     */
    parseModifiers(attrName, property) {
        const parts = attrName.split('.');
        
        return {
            property: property,
            live: parts.includes('live'),
            lazy: parts.includes('lazy'),
        };
    }
}
