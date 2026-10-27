/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Environment-agnostic: talk to whatever host serves the page, with a
// matching scheme — so ONE build works on dev (http + :8080) and
// production (https + :443 via reverse proxy). Only the app key still
// comes from the build (it must match the server's REVERB_APP_KEY).
const reverbUseTLS = window.location.protocol === 'https:';

// Private channels authorize against /broadcasting/auth. Echo defaults to the
// domain root, which breaks when the app lives in a subfolder (production:
// /misco-app/public), so take the full URL Laravel generated in the layout.
const authEndpointMeta = document.querySelector('meta[name="broadcast-auth-endpoint"]');

// Behind the HTTPS reverse proxy the socket lives under this project's own
// path (e.g. /snow/reverb/app/...) so it can't clash with other projects on
// the same domain. Direct http connections to :8080 (dev) need no prefix.
const reverbProxyPath = reverbUseTLS ? (import.meta.env.VITE_REVERB_PATH || '') : '';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    authEndpoint: authEndpointMeta ? authEndpointMeta.content : '/broadcasting/auth',
    wsHost: window.location.hostname,
    wsPort: reverbUseTLS ? 443 : (import.meta.env.VITE_REVERB_PORT ?? 8080),
    wssPort: 443,
    wsPath: reverbProxyPath,
    forceTLS: reverbUseTLS,
    enabledTransports: ['ws', 'wss'],
});
