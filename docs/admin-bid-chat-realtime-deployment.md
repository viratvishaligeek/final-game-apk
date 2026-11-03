# Admin chat, Pusher and notification deployment notes

## Architecture in this change

- Admin Blade pages use one Pusher client per page and subscribe to the authenticated admin's three private channels: admin.payment-alerts, admin.user-presence, and admin.support-chat.
- Each private channel authorization request is expected. Three private subscriptions can produce three /broadcasting/auth requests. That alone is not evidence of duplicate initialization.
- The admin layout owns the global payment/chat event handlers, unread header refresh, and separate payment/chat audio elements. The chat page no longer creates a new audio object for each message.
- The player Vue Live Support page subscribes to private-users.{userId}. It uses the authenticated Sanctum token for /api/broadcasting/auth.
- The Vue page stops its short polling loop while Pusher is connected. A 30-second HTTP recovery poll is used while the connection is unavailable.
- Payment alert sound has priority over chat sound. Chrome autoplay restrictions still apply; admins should click Enable sounds once per browser session when playback is blocked.

## Laravel environment

Set these values on the backend host using the credentials from the actual Pusher Channels application. Do not commit real values.

~~~dotenv
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-app-key
PUSHER_APP_SECRET=your-pusher-app-secret
PUSHER_APP_CLUSTER=your-pusher-cluster
PUSHER_PORT=443
PUSHER_SCHEME=https
~~~

PUSHER_APP_KEY and PUSHER_APP_CLUSTER must match the Vue public values below. Keep PUSHER_APP_SECRET and PUSHER_APP_ID server-side; never expose them in Vite variables.

## Vue environment

Set the public build-time values in the Vue build environment:

~~~dotenv
VITE_PUSHER_APP_KEY=your-pusher-app-key
VITE_PUSHER_APP_CLUSTER=your-pusher-cluster
VITE_PUSHER_AUTH_ENDPOINT=/api/broadcasting/auth
~~~

The public key and cluster are not secrets. The Laravel secret is never sent to the browser.

## Deployment commands

Backend:

~~~bash
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan optimize:clear
php artisan route:list --path=broadcasting
php artisan config:cache
npm ci
npm run build
~~~

Vue app:

~~~bash
npm ci
npm run build
npx cap sync android
~~~

For Android release builds, run the repository's normal signed release pipeline after cap sync; do not use a debug APK as a production release.

No production migration is required by this change. Do not run migrate:fresh. Before any future migration deployment, take a restorable database backup and review docs/database-migration-audit-2026-11.md.

## Verification checklist

1. Confirm Laravel reports BROADCAST_CONNECTION=pusher after config cache rebuild.
2. Confirm the Pusher app ID/key/secret/cluster are the matching application values.
3. Log in as an admin and verify private-channel authorization for all three admin channels.
4. Log in as a player and verify the Sanctum-authenticated /api/broadcasting/auth request for private-users.{id}.
5. Send a message from player to admin and from admin to player.
6. Verify the header unread count is independent per admin and does not double-increment from HTTP plus Pusher.
7. Disconnect network briefly; verify reconnection and the 30-second recovery path.
8. Trigger a gateway sandbox failure to verify payment alert delivery and priority. No real gateway charge is required for this test.
9. Click Enable sounds and test both audio paths. Browser/OS background suspension and autoplay policy may still prevent sound.

No real Pusher credentials, browser session, Android device, or gateway sandbox was available during source editing. End-to-end Pusher delivery and audio playback therefore remain pending until deployment/staging verification.
