# Admin / application migration audit

Audit performed against the Draft PR branch on 2026-11-04. This is a source-tree audit, not a production database audit.

## Critical deployment-state limitation

The repository does not contain the production `migrations` table, a production database snapshot, or a read-only production database connection. Therefore **no migration can honestly be labelled deployed or pending from Git history alone**. File timestamps, commit dates, and the existence of a migration file are not proof that `php artisan migrate` ran on production.

Do not delete, rename, reorder, or rewrite a migration until the production status query below is captured and reviewed. No historical migration has been changed by this audit.

## Migration inventory

| Migration | Operation / area | Production status |
|---|---|---|
| `0001_01_01_000000_create_admins_table.php` | Creates admins and password reset tokens | Unknown |
| `0001_01_01_000002_create_jobs_table.php` | Creates jobs, job batches, failed jobs | Unknown |
| `2026_09_21_135241_games.php` | Creates games | Unknown |
| `2026_09_21_194507_pages.php` | Creates pages | Unknown |
| `2026_09_21_194555_users.php` | Creates users | Unknown |
| `2026_09_23_073128_create_personal_access_tokens_table.php` | Creates Sanctum tokens | Unknown |
| `2026_09_23_093107_password_reset_otps.php` | Creates password reset OTPs | Unknown |
| `2026_09_25_192739_create_permission_tables.php` | Spatie permission schema | Unknown |
| `2026_09_25_202529_faqs.php` | Creates FAQs | Unknown |
| `2026_09_25_210547_create_results_table.php` | Creates results | Unknown |
| `2026_09_26_110058_create_banners.php` | Creates banners | Unknown |
| `2026_09_28_001826_bids.php` | Creates bids with required `order_no` | Unknown |
| `2026_09_28_085439_create_notifications_table.php` | Creates notifications | Unknown |
| `2026_09_28_085557_create_transactions_table.php` | Creates transactions | Unknown |
| `2026_09_28_113346_wallet_requests.php` | Creates wallet requests | Unknown |
| `2026_09_29_161403_game_winners.php` | Creates winners | Unknown |
| `2026_10_07_174850_home_page.php` | Creates home page sections | Unknown |
| `2026_10_07_205935_settings.php` | Creates settings | Unknown |
| `2026_10_09_170000_add_phone_to_bids_table.php` | Adds phone to bids | Unknown |
| `2026_10_10_000001_create_referral_system.php` | Referral columns and rewards table | Unknown |
| `2026_10_10_000002_create_push_subscriptions_table.php` | Creates push subscriptions | Unknown |
| `2026_10_11_000001_add_public_enabled_to_push_subscriptions_table.php` | Adds public-enabled flag | Unknown |
| `2026_10_11_000002_extend_pages_for_navigation_and_seo.php` | Adds navigation and SEO columns | Unknown |
| `2026_10_11_000004_create_community_posts_and_replies.php` | Creates community posts and replies | Unknown |
| `2026_10_12_000001_create_support_chat_tables.php` | Creates support conversations and messages | Unknown |
| `2026_10_12_000002_add_platform_to_faqs_table.php` | Adds FAQ platform | Unknown |
| `2026_10_12_000003_add_site_key_to_domain_owned_records.php` | Adds site key to site-owned tables | Unknown |
| `2026_10_12_000004_make_unique_keys_site_scoped.php` | Replaces global unique indexes with site-scoped keys | Unknown |
| `2026_10_12_000005_add_admin_push_subscriptions.php` | Adds admin ownership to push subscriptions | Unknown |
| `2026_10_23_000001_enforce_notification_retention_limit.php` | Adds notification retention index | Unknown |
| `2026_10_23_000002_add_user_notification_preference.php` | Adds user notification preference | Unknown |
| `2026_10_23_000003_add_gateway_payment_url.php` | Adds gateway payment URL to wallet requests | Unknown |
| `2026_10_24_000001_add_admin_credit_audit_to_transactions_table.php` | Adds admin credit audit/idempotency fields | Unknown |
| `2026_11_03_000001_create_admin_payment_alerts_tables.php` | Creates admin payment alerts and reads | Unknown |
| `2026_11_03_000002_add_user_presence_tracking.php` | Adds user presence fields and session table | Unknown |
| `2026_11_03_000003_create_support_conversation_admin_reads.php` | Creates per-admin conversation read state | Unknown |
| `2026_11_03_000004_expand_admin_payment_alert_diagnostics.php` | Adds payment alert diagnostics | Unknown |

## Same-table migration chains to assess after production status is known

- `bids`: base table + `phone` column.
- `pages`: base table + navigation/SEO fields.
- `faqs`: base table + platform field.
- `push_subscriptions`: base table + public-enabled + admin ownership + site-scoped uniqueness.
- `users`: base table + referral fields + site key + notification preference + presence fields.
- `settings`: base table + site key + site-scoped unique key.
- `notifications`: base table + retention index.
- `wallet_requests`: base table + gateway payment URL.
- `transactions`: base table + admin credit audit/idempotency fields.
- `admin_payment_alerts`: initial tables + diagnostic expansion.

These are not automatically redundant. Several later migrations encode data-preserving index transitions, foreign keys, and rollback behavior. A consolidation is only safe when every migration in the proposed chain is absent from every deployed environment, the target schema is verified, and the resulting single migration preserves all constraints and data semantics.

## Required production evidence

Run against the production database using the deployed release's Laravel code and a read-only or approved operational session:

```bash
php artisan migrate:status --database=mysql
```

Capture the output alongside:

```sql
SELECT migration, batch
FROM migrations
ORDER BY batch, migration;
```

Also capture the production schema for the affected tables before proposing consolidation. Compare the result against this inventory, mark each migration `Ran` or `Pending`, and only then prepare a separate reviewed consolidation plan. Do not run `migrate:fresh`, `migrate:refresh`, or rollback commands on production as part of this audit.

## Findings

- The base bids migration declares `order_no` as a non-null string. The legacy grouping query nevertheless isolates both NULL and empty identifiers by bid id; the regression test uses empty string because that is representable under the repository's current schema.
- No migration was deleted, renamed, rewritten, reordered, or consolidated.
- Production deployed/pending state remains **unverified** pending the live `migrations` table and schema evidence.
