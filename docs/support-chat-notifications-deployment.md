# Support chat, notification and multi-domain deployment notes

## Database migration
1. Back up the production database and review the migrations before deployment.
2. Run php artisan migrate --force after reviewing the diff. These are additive migrations; do not run migrate:fresh or the repository's legacy /fresh-db route.
3. The migrations add support conversations/messages, FAQ platform targeting, and a site_key to existing domain-owned tables. Existing records are assigned to config('sites.default') (default: play-online-khaiwal). Existing content is not automatically duplicated to the second domain.

## Domain/site configuration and isolation
- Configure domains in config/sites.php and set SITE_DEFAULT_KEY, SITE_PLAY_ONLINE_KHAIWAL_URL, and SITE_GALIDISAWAR_URL as appropriate.
- Production hosts not listed in config/sites.php return 404 when SITES_STRICT_HOSTS=true (the production default). Add every legitimate host alias before enabling strict host checks.
- The admin sidebar site selector persists the selected site in the admin session. Eloquent scopes on domain-owned models isolate records by site_key; the active site also has an independent settings cache and FAQ cache.
- Review custom DB::table() queries and third-party jobs before using multiple domains in production; raw query-builder access bypasses model global scopes. Do not assume that a custom query is site-scoped unless it explicitly filters site_key.
- Existing rows are assigned to the default site during migration. If a second domain already has live data, classify and assign those records to the correct site before routing traffic to it.

## Private chat attachments
- Local private storage remains the safe default for existing installs. Before enabling S3, add the adapter with composer require league/flysystem-aws-s3-v3 and commit the resulting composer.json and composer.lock changes. Then set SUPPORT_CHAT_DISK=s3 and configure AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_BUCKET, and (for compatible providers) AWS_ENDPOINT / AWS_USE_PATH_STYLE_ENDPOINT.
- Before switching the app to S3, configure the S3 adapter dependency in Composer, then run php artisan support-chat:migrate-media-to-s3 (use --dry-run first). The command copies files and intentionally keeps the local originals until you verify the destination bucket.
- Set SUPPORT_CHAT_PRESIGNED_URL_MINUTES=5 or another short expiry. Keep the bucket private and configure bucket CORS for the exact web origins that must play attachments. Never make the bucket public. The app redirects authenticated media requests to short-lived signed URLs when the S3 disk is enabled.
- Test upload, playback and expiry against the target object-storage provider before switching production traffic. The app cannot verify provider credentials from source control.

## Chat delivery and real-time transport
- Current chat delivery still uses authenticated HTTP polling every three seconds. It is not WebSocket or server-push real-time messaging. No Reverb/Echo/Pusher server configuration or connection credentials are included in the repository; install and configure the chosen WebSocket/broadcast service before claiming scalable real-time delivery.
- User chat messages are persisted in the database. Admin replies create a user notification and attempt FCM delivery when Firebase server credentials are configured.
- The admin page can show browser desktop alerts and play the configured chat sound while the dashboard is open. This is not a background push notification when the admin has closed the page. Admin-side background push requires a separately registered admin FCM subscription and valid web-push/service-worker configuration.
- Voice recording uses the browser MediaRecorder API and requires microphone permission plus a secure context (HTTPS, except localhost). Supported formats depend on the browser. Files are limited to 20 MB and server MIME validation is enforced.

## FAQ-first support
- Admins can target FAQs to web, app or both. The public FAQ endpoint caches by both site and platform for five minutes.
- The Vue support screen presents FAQs first; the user explicitly escalates to a human conversation when the FAQ does not resolve the issue.

## Notification retention and sounds
- Private per-user notification rows are pruned to the latest ten whenever a new private notification is created. Public announcements (user_id=null) are shared site-level records and are not deleted as a side effect of one user's retention cleanup.
- Admins can mute individual in-app sound categories. The repository currently has one confirmed web WAV fallback (public/notification-tune.wav); event-specific MP3s are not present. Native OS push sounds are controlled separately by the mobile platform's notification channel/settings and are not dynamically changed by the web sound selector.
- Push links use the resolved site's configured URL rather than a hard-coded unrelated domain. FCM delivery still requires valid FCM_PROJECT_ID and FCM_SERVICE_ACCOUNT_JSON secrets in the server environment.

## Production API URL
For a Vue web build served from the Laravel domain, the default API base is same-origin /api/v1. For a separately hosted frontend or Capacitor native build, set VITE_API_BASE_URL to the full HTTPS Laravel API base before building.
