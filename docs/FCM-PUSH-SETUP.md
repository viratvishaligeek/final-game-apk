# FCM push notification setup and verification

This project uses Firebase Cloud Messaging HTTP v1 for server sends, Capacitor Push Notifications for native app registration, and Firebase Web Messaging for browser subscriptions.

## Required server configuration

Set these in the Laravel deployment environment (never commit service-account credentials):

- **FCM_PROJECT_ID**: Firebase project ID used by the HTTP v1 endpoint.
- **FCM_SERVICE_ACCOUNT_JSON**: the complete Firebase service-account JSON as a single environment value. The service account must have permission to send Firebase Cloud Messaging messages.
- **FIREBASE_WEB_API_KEY**, **FIREBASE_WEB_AUTH_DOMAIN**, **FIREBASE_WEB_PROJECT_ID**, **FIREBASE_WEB_MESSAGING_SENDER_ID**, **FIREBASE_WEB_APP_ID**, and **FIREBASE_WEB_VAPID_KEY**: public Firebase Web SDK settings and the Web Push certificate key. These are public client settings, not private credentials.

The web project ID must match the project that issues the FCM registration tokens. The sender validates the service-account project ID against FCM_PROJECT_ID when that field is present.

After changing Laravel environment values, run:

```sh
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
```

There is no queue job in the current push sender: FCM calls are synchronous. A queue-worker restart is not required for these sends.

## Required Vue/Android configuration

For the Vue build, provide the public VITE_FIREBASE_API_KEY, VITE_FIREBASE_AUTH_DOMAIN, VITE_FIREBASE_PROJECT_ID, VITE_FIREBASE_MESSAGING_SENDER_ID, VITE_FIREBASE_APP_ID, and VITE_FIREBASE_VAPID_KEY values. Production builds now fail rather than silently generating an empty web-push configuration.

For native Android builds, place the Firebase Android configuration downloaded from Firebase Console at **android/app/google-services.json**.

It must belong to Firebase project/app ID **com.galidisawar.playgame**. This file is intentionally not committed. The Gradle build now stops if it is missing rather than producing an APK that cannot register with FCM.

Build and sync the app:

```sh
npm ci
npm run build:android
```

Install the newly built APK. The app uses a versioned notification channel (**game-alerts-v2**) so an old, previously-created silent Android channel does not silently retain its old sound configuration. Users can still change channel sound/importance in Android settings.

## Public and private targeting

- Website guests and app installations register a token through **POST /api/v1/push/subscribe**; the token is stored in the existing push_subscriptions table with public_enabled=true.
- Authenticated app devices register through **POST /api/v1/push/register-user**. Individual sends query only subscriptions whose user_id matches the intended user.
- Unsubscribe disables public eligibility. If a token is still linked to a user, its row remains available for private notifications; otherwise the row is removed. Website unsubscribe does not change OS permission settings. Logout detaches only the current device from the account.
- Public broadcast sends only rows with public_enabled=true, including eligible authenticated app devices. Token-hash upsert prevents duplicate rows for the same token, but a browser token and a separate native-app token are distinct subscriptions and can both receive a public event.
- Wallet-request approval/rejection and game reward notifications are persisted as user-scoped records and sent only to the relevant user's registered tokens.

## Reproducible verification

1. Confirm the deployed environment has the required Firebase values without printing the service-account JSON to logs or chat.
2. Confirm /firebase-config.json returns the public web config and VAPID key (never service-account fields).
3. In the Laravel website, click **Subscribe**, grant browser permission, and confirm the browser returns a token and POST /api/v1/push/subscribe returns success.
4. On an Android device, install the rebuilt APK, allow notifications, and confirm the backend logs a token registration without logging the token itself.
5. Trigger an admin public broadcast. Confirm the Laravel log records an FCM message ID for accepted requests; this proves FCM acceptance only, not OS display.
6. Test foreground, background, and terminated states on a physical device. Verify panel display and sound, then repeat with app notifications disabled and with the channel muted.
7. Approve/reject a test wallet request and publish a test result with a winner. Confirm private notification records use the intended user_id and only that user's registered subscription IDs appear in individual-send logs.

No physical-device delivery test or Firebase Console credential check can be claimed from repository inspection alone. Those steps must be run against the deployed environment and target devices.