# Storage and real-time deployment notes

## Storage decision

The application keeps Laravel's existing local disks as the default. This avoids requiring a paid cloud account or cloud credentials for local development and ordinary cPanel deployments. New banner uploads, support-chat attachments, and notification-sound uploads now go through `App\\Services\\ProjectStorage`, which selects disks from `config/project-storage.php`.

For remote object storage, Cloudflare R2 is the recommended optional provider because Laravel's S3 filesystem adapter can speak to its S3-compatible API and R2 does not charge for internet egress. It is not configured by this change and no account or credentials have been created.

Cloudflare's published Standard free allowance currently lists 10 GB-month storage, 1 million Class A operations, 10 million Class B operations, and free egress per month. Usage above the allowance is billed; the listed Standard rates are $0.015/GB-month, $4.50/million Class A requests, and $0.36/million Class B requests. Monitor usage and configure billing alerts. Free allowance and prices can change. See https://developers.cloudflare.com/r2/pricing/.

Supabase Storage is an alternative, but its current free plan lists 1 GB file storage and 5 GB egress, and free projects can be paused after inactivity. See https://supabase.com/docs/guides/storage/pricing and https://supabase.com/pricing.

## Local/private storage (works without cloud credentials)

Keep these defaults in place:

```dotenv
PROJECT_PUBLIC_STORAGE_DISK=public
PROJECT_PRIVATE_STORAGE_DISK=local
PROJECT_MAX_UPLOAD_KB=20480
```

The `public` disk stores banners and notification sounds under Laravel's public storage root. Run `php artisan storage:link` if the deployment serves these files through Laravel's standard `/storage` symlink. The `local` disk is private and support attachments are served only through authenticated controller routes.

Do not change the private disk to a public disk. Existing stored paths are not automatically migrated by changing these settings.

## Optional Cloudflare R2 setup

1. Create/sign in to a Cloudflare account and open **Storage & databases → R2**. Complete the R2 subscription setup. R2's included free allowance is usage-limited, not unlimited.
2. Create a bucket. Keep the private object prefix inaccessible to anonymous users. If public banners/audio are needed, use a separate public custom domain/bucket policy and do not expose the private prefix.
3. Create an R2 API token scoped to this bucket with only the object read/write permissions the app needs. Keep the access key and secret exclusively on the Laravel server.
4. Install the Laravel S3 filesystem adapter in a controlled dependency update, then commit the matching Composer lockfile. This repository's current Composer manifest does not include that adapter, so the R2 disks below cannot be used until the adapter dependency is installed and verified:
   `composer require league/flysystem-aws-s3-v3`
5. Configure server environment variables (never in Vue/Vite variables):

```dotenv
R2_ACCESS_KEY_ID=replace-with-server-side-access-key
R2_SECRET_ACCESS_KEY=replace-with-server-side-secret
R2_REGION=auto
R2_BUCKET=replace-with-bucket-name
R2_ENDPOINT=https://replace-with-account-id.r2.cloudflarestorage.com
R2_PUBLIC_URL=https://assets.example.com
PROJECT_PUBLIC_STORAGE_DISK=r2-public
PROJECT_PRIVATE_STORAGE_DISK=r2-private
```

6. Point `R2_PUBLIC_URL` at the public custom domain configured for the public object prefix. Do not publish the private prefix. R2 public custom-domain configuration and bucket access policy must be verified in the Cloudflare dashboard; this repository cannot configure an external account.
7. Clear cached configuration with `php artisan config:clear` (or rebuild config cache after validating the values). Upload a test banner and sound; upload a support attachment and confirm its API media URL is authenticated/short-lived. Verify preview, download, and deletion before switching production disks.
8. Monitor storage and Class A/B operations monthly. Set budget/usage alerts. Back up important files and keep a verified inventory of old local paths. Do not delete or migrate existing files until every old path has been checked and a backup restored successfully in a test environment.

Laravel-side upload and URL generation remains provider-independent through `ProjectStorage`; legacy upload locations still require a separate audit/migration before claiming all project files are on the shared abstraction.

## Real-time chat deployment blocker

The current codebase does not yet contain Laravel Reverb, Laravel Echo, or the Pusher protocol client dependency/lockfile updates. The existing chat refresh remains adaptive polling and is **not WebSocket real-time**. Do not remove the recovery polling until the end-to-end WebSocket path has been implemented and verified.

The intended self-hosted provider is Laravel Reverb (open source, no per-message hosted-provider fee), subject to the server being able to run a long-lived PHP process and route secure WebSocket traffic. Official Laravel 13 instructions: https://github.com/laravel/docs/blob/13.x/reverb.md.

A controlled implementation/deployment needs:
- Add compatible Reverb and Echo dependencies and commit both lockfiles.
- Run `php artisan install:broadcasting`, inspect the generated files, and configure private-channel authorization for the actual user/admin guards.
- Set the `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME`, and `VITE_REVERB_APP_KEY` / `VITE_REVERB_HOST` / `VITE_REVERB_PORT` / `VITE_REVERB_SCHEME` values as appropriate. Secrets stay server-side; only the public app key and endpoint belong in the frontend build.
- Run `php artisan reverb:start` under Supervisor/systemd or the hosting provider's process manager. Use HTTPS/WSS via a reverse proxy; the proxy must support WebSocket upgrade headers. Start a queue worker if events are queued.
- Test two authenticated clients, unauthorized channel access, reconnect/replay recovery, and duplicate delivery before removing polling.

Shared hosting often does not allow persistent WebSocket processes or custom reverse-proxy routes. Verify those capabilities with the host first; if unavailable, Reverb cannot be deployed reliably on that cPanel plan without moving the WebSocket process to a supported server.
