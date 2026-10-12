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

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort: reverbUseTLS ? 443 : (import.meta.env.VITE_REVERB_PORT ?? 8080),
    wssPort: 443,
    forceTLS: reverbUseTLS,
    enabledTransports: ['ws', 'wss'],
});
