import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '192.168.1.4', // Allows connections from other devices
        cors: true, // <--- ADD THIS LINE to fix the CORS error
        hmr: {
            host: '192.168.1.4', // Your IPv4 address
        },
        watch: {
            usePolling: true, // This ensures Windows catches the "Save" event
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
