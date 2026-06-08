import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // Silence deprecation warnings from Bootstrap/FA
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
            },
        },
    },
    build: {
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    // Keep webfonts in a webfonts/ folder so FA CSS url() paths resolve
                    if (/\.(woff2?|ttf|eot|svg)$/.test(assetInfo.name ?? '')) {
                        return 'assets/webfonts/[name][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                },
            },
        },
    },
});
