# Automatic payment error monitoring — deployment and validation

## What this change does

- Records a persistent admin alert for an automatic gateway order failure, missing gateway configuration, invalid provider response/payment URL, verification failure, provider-confirmed payment failure, and caught backend exceptions.
- Creates an internal attempt ID before calling the gateway. A gateway transaction ID is optional and is never substituted with the internal attempt ID.
- Stores a sanitized error summary, category, code (when returned), HTTP status, processing stage, diagnostic reference, severity, and repeat count. It does not store request bodies, API keys, access tokens, authorization headers, card data, OTPs, or stack traces in the alert record.
- Uses one alert row per payment attempt. Repeated callbacks for the same attempt update its occurrence count and do not rebroadcast a second modal alert.
- Keeps acknowledgement state per admin account.
- Returns generic user-facing errors for gateway and server failures; field validation errors remain distinguishable.

## Classification rules

- PAYMENT_GATEWAY_CONFIGURATION_ERROR: HTTP 401/403 responses, and the exact normalized provider message "Active Plan not found. Please Buy Plan". The admin wording asks to verify the merchant plan/account; it does not claim that an expiry has been proven.
- PAYMENT_GATEWAY_API_ERROR: provider/upstream API errors, timeouts, invalid order responses, and HTTP 5xx errors.
- BACKEND_SYSTEM_ERROR: caught backend exceptions and failed payment-attempt persistence.
- PAYMENT_VERIFICATION_ERROR: provider verification/callback responses that cannot be verified against the stored attempt.
- PAYMENT_FAILURE: a provider-verified failure status.
- Input validation and explicit user cancellation/expiry do not create critical payment alerts.

The configured EKQR integration currently has no checked-in provider error-code reference. Therefore this change does not invent EKQR-specific error-code meanings. The exact message above is recognized as a gateway configuration issue; unknown messages/codes are shown in sanitized form for investigation. Add further code mappings only after confirming the provider's official documentation.

## Broadcasting configuration

The repository has pusher/pusher-php-server in composer.json and composer.lock. laravel/reverb is not installed in the current dependency lock. The supported production path for this branch is therefore Pusher Channels.

Set these server-side environment variables using values from the actual Pusher application. Do not commit credentials:

```dotenv
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-real-app-id
PUSHER_APP_KEY=your-real-app-key
PUSHER_APP_SECRET=your-real-app-secret
PUSHER_APP_CLUSTER=your-real-cluster
```

Then run:

```bash
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
```

Confirm the Pusher application allows WebSocket connections from the admin site's origin and that outbound HTTPS/WebSocket traffic is permitted. The admin page uses the Pusher JS client and authenticated /broadcasting/auth; the route is protected by the admin session and admin.site middleware, and the channel callback checks the active admin guard.

Do not set BROADCAST_CONNECTION=reverb on this branch as-is. Reverb config keys exist in config/broadcasting.php, but laravel/reverb is absent from the lock file. Install and validate that package, configure a Reverb server/reverse proxy and credentials, and then enable the Reverb client branch before selecting it.

When broadcasting is disabled or unavailable, pending alerts are still restored by the authenticated HTTP pending-alert endpoint after page load/refresh. Real-time delivery cannot work until a real broadcaster and valid credentials are configured.

## Database rollout

Back up the database, then deploy the code and run:

```bash
php artisan migrate --force
```

The new migration extends admin_payment_alerts and makes wallet_request_id nullable so a payment-attempt alert can still be represented when a request row was not persisted but the database recovered enough to save the alert. If the database itself remains unavailable, no same-database alert can be guaranteed; the failure is written to the protected application log with a diagnostic reference.

## Tests

The branch adds unit tests for classification and secret redaction, and a feature assertion for the admin broadcast-auth route. The backend workflow runs migrations and the full Laravel suite on this branch. Frontend CI runs unit tests, build, lint, and Android build.

The following still require an environment with valid Pusher credentials and a test gateway account: browser login, WebSocket subscription/delivery, two-admin acknowledgement isolation, reconnection, sound playback, and provider sandbox callbacks. Do not use production charges to test these scenarios.
