# Admin bid and chat realtime deployment notes

## Scope

This change adds no database migrations. It uses the existing bid indexes, the unique `support_conversations.user_id` constraint, the per-admin `support_conversation_admin_reads` table, and existing Pusher channels.

The admin browser uses one shared Pusher JS client per page load. Payment alerts, presence and support chat use site-qualified private channels; the channel authorization callback also verifies that the authenticated admin's selected site matches the channel. The three admin private-channel authorizations are separate expected `/broadcasting/auth` requests. The player app authorizes its own `private-users.{id}` channel at `/api/broadcasting/auth`. Repeated requests beyond these subscriptions should be investigated for duplicate page initialization or duplicate subscriptions; do not assume each authorization request is an error.

## Backend environment

Configure these values on the Laravel host using credentials from the intended Pusher application. Do not commit real values.

```dotenv
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-public-key
PUSHER_APP_SECRET=your-pusher-app-secret
PUSHER_APP_CLUSTER=your-pusher-cluster
PUSHER_PORT=443
PUSHER_SCHEME=https
```

For Pusher Cloud, leave `PUSHER_HOST` unset and use the correct cluster. The app key and cluster must match the Vue build. Do not copy placeholder values into production.

The backend already requires `pusher/pusher-php-server`. After updating the Laravel environment:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
php artisan route:list --path=broadcasting
```

The support-message event implements `ShouldBroadcastNow` and `ShouldDispatchAfterCommit`; that event does not require a queue worker. Do not infer that unrelated queued events need no worker.

## Vue build environment

The player application reads:

```dotenv
VITE_PUSHER_APP_KEY=your-pusher-public-key
VITE_PUSHER_APP_CLUSTER=your-pusher-cluster
VITE_PUSHER_AUTH_ENDPOINT=/api/broadcasting/auth
```

The first two values must match the backend Pusher application. The auth endpoint is a same-origin Laravel API route protected by Sanctum and the active-user middleware. The bearer token is sent by the app at runtime; never place a user token in a Vite environment file.

Build and deploy the Vue assets using the existing deployment process:

```bash
npm ci
npm run test:unit
npm run build
```

For Android packaging, use the repository's existing `npm run build:android` command and the configured Android toolchain.

## Browser sound policy

Chrome and other browsers may block audio before a user gesture. Admins should use the persistent **Enable sound** control once in each browser profile. The app attempts playback on incoming events and displays a status if playback is blocked. It cannot guarantee sound when the browser, operating system, or device suspends the background tab.

Payment failure audio has priority over chat audio. Critical payment alerts remain queued and individually acknowledged. The 30-second HTTP recovery check is only active when the Pusher connection is unavailable; the support-chat page also stops its own recovery timer while the shared client is connected.

## Verification before production

1. Confirm the Pusher app ID, key, secret and cluster in the hosting environment without exposing them in logs.
2. Confirm `BROADCAST_CONNECTION=pusher` after configuration cache is rebuilt.
3. Sign in as an active admin and confirm the three expected admin private channels authorize.
4. Sign in as a player and confirm only that user's private channel authorizes.
5. Send a player message and verify delivery to admin sessions; send an admin reply and verify delivery to that player.
6. Verify the per-admin unread count remains independent across two admin accounts.
7. Trigger a test payment failure in a non-production environment and verify queueing, acknowledgement and sound behaviour.
8. Check browser network logs: one authorization per private subscription is expected; repeated subscriptions from the same page lifecycle are not.
9. Verify the recovery check stops when Pusher reconnects and resumes only on disconnect.
10. Do not deploy until the valid Pusher infrastructure and external browser connectivity have been verified.
