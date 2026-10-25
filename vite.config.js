import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // Keep the existing Laravel font asset pipeline intact.
                // Frontend typography is currently supplied by its existing layout.
            ],
        }),
    ],
    base: './',
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
