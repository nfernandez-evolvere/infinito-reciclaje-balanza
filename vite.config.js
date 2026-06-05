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
        // En Docker el proceso escucha en todas las interfaces (0.0.0.0).
        // hmr.host le dice al browser a qué host conectarse para HMR/refresh:
        // como el browser corre en el host Windows, usa localhost (que Docker
        // mapea al contenedor vía el puerto 5173 expuesto en compose.dev.yaml).
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        watch: {
            // usePolling: requerido en Docker Desktop + Windows (WSL2).
            // inotify no recibe eventos del filesystem Windows a través del
            // bind mount → Vite nunca detecta los cambios sin polling.
            usePolling: true,
            interval: 1000,
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
