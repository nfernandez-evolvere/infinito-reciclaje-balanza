import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

/**
 * Configuración resiliente al deploy. Precedencia de cada valor:
 *   1) meta tag (config de runtime, ver layouts/head.blade.php) — la misma imagen
 *      sirve cualquier entorno sin rebuild,
 *   2) VITE_REVERB_* horneadas en el build (dev),
 *   3) el origen de la página (producción detrás del edge, que proxea /app).
 *
 * La app key no es secreta. host/puerto/esquema PÚBLICOS son distintos del
 * REVERB_HOST interno que usa el publisher PHP.
 */
const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content || undefined;

const scheme = meta('reverb-scheme')
    || import.meta.env.VITE_REVERB_SCHEME
    || (window.location.protocol === 'https:' ? 'https' : 'http');
const forceTLS = scheme === 'https';
const host = meta('reverb-host') || import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const port = Number(meta('reverb-port') || import.meta.env.VITE_REVERB_PORT) || (forceTLS ? 443 : 80);
const key = meta('reverb-key') || import.meta.env.VITE_REVERB_APP_KEY;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key,
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS,
    enabledTransports: forceTLS ? ['wss'] : ['ws'],
});
