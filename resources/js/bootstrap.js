import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// THE FIX: Pilitin ang Axios na ipadala ang Session Cookies sa /broadcasting/auth para makilala ang user at maiwasan ang 403 Forbidden!
window.axios.defaults.withCredentials = true;

// ==========================================
// LARAVEL ECHO & REVERB CONFIGURATION
// ==========================================
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
  wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
});
