# Support chat and notification deployment notes

## Database and storage
1. Deploy the Laravel branch and run `php artisan migrate --force` after taking a database backup. The migration adds `support_conversations` and `support_messages`; it does not reset existing tables.
2. Ensure Laravel's configured local storage disk is writable. Chat media is stored privately and served through authenticated, conversation-authorized download endpoints; no public storage symlink is required.
3. Chat polling uses authenticated HTTP requests every three seconds; it does not require a WebSocket server or Redis. This is suitable for shared cPanel hosting. Requests are throttled and protected by Sanctum for users and the admin guard for the dashboard.
4. Chat uploads are limited to supported image/video/audio MIME types and 20 MB per file. User downloads are scoped to their own conversation; admin downloads require the admin guard.

## Result notification toggle
The result page stores `result_notifications_enabled` in the existing settings table through `AppSettingsService`. Missing setting defaults to ON. OFF prevents the result controller from creating the public result notification and from dispatching winner/result alerts; result persistence and settlement still run.

## Notification sounds
The Vue application currently contains `public/notification-tune.wav` and the Android raw resource `notification_tune.wav`. No event-specific MP3s were found in the repository. The client sound service maps all six requested event categories to the existing WAV fallback; add the six approved MP3 files and update `src/services/notificationAudio.js` when available.

## Production API URL
For a Vue web build served from the Laravel domain, the default API base is same-origin `/api/v1`. For a separately hosted frontend or Capacitor native build, set `VITE_API_BASE_URL` to the full HTTPS Laravel API base (for example, `https://playonlinekhaiwal.com/api/v1`) before building.
