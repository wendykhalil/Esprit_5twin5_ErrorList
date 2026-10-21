import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

/**
 * Laravel Echo configuration for real-time broadcasting
 *
 * Connects to Reverb WebSocket server for real-time event broadcasting.
 * Uses private channels for authenticated users with role-based authorization.
 *
 * Configuration:
 * - Broadcaster: reverb
 * - Host: localhost (configurable via REVERB_HOST env var)
 * - Port: 8080 (configurable via REVERB_PORT env var)
 * - Auth: via /broadcasting/auth endpoint
 */

// Sans clé Reverb, new Echo() lève une exception qui stoppait tout le bundle (Alpine, graphiques, conseiller).
if (import.meta.env.VITE_REVERB_APP_KEY) {
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT,
            wssPort: import.meta.env.VITE_REVERB_PORT,
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
            auth: {
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
                },
            },
    });
    } catch (error) {
        console.warn('Echo indisponible, temps réel désactivé.', error);
    }
}

export default window.Echo;
