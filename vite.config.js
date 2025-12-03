import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        lib: {
            entry: resolve(__dirname, 'resources/js/Diffyne.js'),
            name: 'Diffyne',
            fileName: 'diffyne',
            formats: ['iife']
        },
        outDir: 'public/js',
        emptyOutDir: true,
        rollupOptions: {
            output: {
                entryFileNames: 'diffyne.js',
                assetFileNames: 'diffyne.[ext]',
                inlineDynamicImports: true
            }
        },
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: false,
                drop_debugger: true,
                pure_funcs: [], // Don't remove any functions
            },
            mangle: {
                // Preserve class names for debugging
                keep_classnames: true,
                keep_fnames: false,
            },
            format: {
                comments: false,
            }
        },
        sourcemap: false
    }
});
