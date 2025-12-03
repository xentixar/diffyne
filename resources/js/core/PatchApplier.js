/**
 * PatchApplier.js
 * Applies Virtual DOM patches to actual DOM (Single Responsibility)
 */

import { VNodeConverter } from './VNodeConverter.js';

export class PatchApplier {
    constructor() {
        this.converter = new VNodeConverter();
    }

    /**
     * Apply all patches to a component
     */
    applyPatches(contentRoot, patches) {
        // Check for full replacement
        if (this.isFullReplacement(patches)) {
            return this.replaceContent(contentRoot, patches[0]);
        }

        // Apply individual patches
        patches.forEach(patch => this.applyPatch(contentRoot, patch));
    }

    /**
     * Check if patches represent a full content replacement
     */
    isFullReplacement(patches) {
        if (patches.length !== 1) return false;
        
        const patch = patches[0];
        const type = patch.t || patch.type;
        const path = patch.p || patch.path;
        
        return (type === 'c' || type === 'create') && 
               (path?.length === 0 || !path);
    }

    /**
     * Replace entire content
     */
    replaceContent(contentRoot, patch) {
        const data = patch.d || patch.data;
        const newContent = this.converter.vnodeToDOM(data.node);
        contentRoot.parentNode.replaceChild(newContent, contentRoot);
        return true;
    }

    /**
     * Apply a single patch
     */
    applyPatch(root, patch) {
        const type = this.expandType(patch.t || patch.type);
        const path = patch.p || patch.path;
        const data = patch.d || patch.data;
        
        let target, insertIndex;
        
        if (type === 'create' && path.length > 0) {
            const parentPath = path.slice(0, -1);
            insertIndex = path[path.length - 1];
            target = this.getNodeByPath(root, parentPath);
        } else {
            target = this.getNodeByPath(root, path);
            insertIndex = null;
        }

        if (!target) return;

        switch (type) {
            case 'create':
                this.patchCreate(target, data, insertIndex);
                break;
            case 'remove':
                this.patchRemove(target);
                break;
            case 'replace':
                this.patchReplace(target, data);
                break;
            case 'update_text':
                this.patchUpdateText(target, data);
                break;
            case 'update_attrs':
                this.patchUpdateAttrs(target, data);
                break;
            case 'reorder':
                this.patchReorder(target, data);
                break;
        }
    }

    /**
     * Expand minified patch type
     */
    expandType(type) {
        const typeMap = {
            'c': 'create',
            'r': 'remove',
            'R': 'replace',
            't': 'update_text',
            'a': 'update_attrs',
            'o': 'reorder'
        };
        return typeMap[type] || type;
    }

    /**
     * Get DOM node by path
     */
    getNodeByPath(root, path) {
        let node = root;

        for (const index of path) {
            if (!node) return null;
            
            const meaningfulChildren = Array.from(node.childNodes).filter(child => {
                if (child.nodeType === Node.TEXT_NODE) {
                    return child.textContent.trim() !== '';
                }
                return true;
            });
            
            if (!meaningfulChildren[index]) return null;
            node = meaningfulChildren[index];
        }

        return node;
    }

    /**
     * Create new node
     */
    patchCreate(parent, data, insertIndex = null) {
        const newNode = this.converter.vnodeToDOM(data.node);
        
        if (insertIndex !== null) {
            const meaningfulChildren = Array.from(parent.childNodes).filter(child => {
                if (child.nodeType === Node.TEXT_NODE) {
                    return child.textContent.trim() !== '';
                }
                return true;
            });
            
            const referenceNode = meaningfulChildren[insertIndex];
            if (referenceNode) {
                parent.insertBefore(newNode, referenceNode);
            } else {
                parent.appendChild(newNode);
            }
        } else {
            parent.appendChild(newNode);
        }
    }

    /**
     * Remove node
     */
    patchRemove(node) {
        node.parentNode?.removeChild(node);
    }

    /**
     * Replace node
     */
    patchReplace(oldNode, data) {
        const newNode = this.converter.vnodeToDOM(data.node);
        oldNode.parentNode?.replaceChild(newNode, oldNode);
    }

    /**
     * Update text content
     */
    patchUpdateText(node, data) {
        if (node.nodeType === Node.TEXT_NODE) {
            node.textContent = data.x || data.text;
        }
    }

    /**
     * Update attributes
     */
    patchUpdateAttrs(element, data) {
        const setAttrs = data.s || data.set || {};
        const removeAttrs = data.r || data.remove || [];
        
        Object.entries(setAttrs).forEach(([key, value]) => {
            element.setAttribute(key, value);
            
            if ((element.tagName === 'INPUT' || element.tagName === 'TEXTAREA' || 
                 element.tagName === 'SELECT') && key === 'value') {
                element.value = value;
            }
        });

        removeAttrs.forEach(key => {
            element.removeAttribute(key);
            
            if ((element.tagName === 'INPUT' || element.tagName === 'TEXTAREA' || 
                 element.tagName === 'SELECT') && key === 'value') {
                element.value = '';
            }
        });
    }

    /**
     * Reorder children
     */
    patchReorder(parent, data) {
        data.moves.forEach(move => {
            const node = parent.childNodes[move.from];
            if (node) {
                parent.insertBefore(node, parent.childNodes[move.to]);
            }
        });
    }
}
