import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Keep one Echo instance per document for Vite-powered admin pages.
if (!window.Echo) {
    const key = import.meta.env.VITE_PUSHER_APP_KEY;
    const cluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;

    if (key && cluster) {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key,
            cluster,
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
            },
        });
    } else {
        console.warn('Admin Echo was not initialized: VITE_PUSHER_APP_KEY and VITE_PUSHER_APP_CLUSTER are required.');
    }
}
