# Database migration review — November 2026

## Scope and safety decision

The repository contains **37 migration files** on the reviewed `main` baseline. This branch does not rewrite or delete historical migrations and does not add a schema migration.

The repository is already carrying deployed application history (the prior Admin/Chat/Presence work was merged into `main`). The live production `migrations` table and production schema were not accessible during this code-only review. Therefore it is not possible to prove which incremental migrations have already run in production. Rewriting the original table-creation migrations and deleting later migration files without that evidence would make rollback history unreliable and could make existing environments differ from fresh installs.

**Decision:** retain all 37 migration files. This is intentional production-safety preservation, not a claim that every incremental migration is architecturally ideal. A consolidation release should be done only after capturing production migration status and schema, and should preserve the existing migration ledger.

## Inventory

| Migration | Role / reviewed concern |
|---|---|
| `0001_01_01_000000_create_admins_table.php` | Core admin table |
| `0001_01_01_000002_create_jobs_table.php` | Queue infrastructure |
| `2026_09_21_135241_games.php` | Core games table |
| `2026_09_21_194507_pages.php` | Core CMS pages table |
| `2026_09_21_194555_users.php` | Core users table |
| `2026_09_23_073128_create_personal_access_tokens_table.php` | Sanctum tokens |
| `2026_09_23_093107_password_reset_otps.php` | Password reset OTP records |
| `2026_09_25_192739_create_permission_tables.php` | Spatie roles/permissions |
| `2026_09_25_202529_faqs.php` | Core FAQ table |
| `2026_09_25_210547_create_results_table.php` | Game results |
| `2026_09_26_110058_create_banners.php` | Banners |
| `2026_09_28_001826_bids.php` | Core bids; includes order number, user/game/date/type/number/amount/status and aggregation indexes |
| `2026_09_28_085439_create_notifications_table.php` | User notifications |
| `2026_09_28_085557_create_transactions_table.php` | Wallet ledger |
| `2026_09_28_113346_wallet_requests.php` | Add/withdraw requests |
| `2026_09_29_161403_game_winners.php` | Winner records |
| `2026_10_07_174850_home_page.php` | Homepage builder |
| `2026_10_07_205935_settings.php` | Settings |
| `2026_10_09_170000_add_phone_to_bids_table.php` | Incremental nullable bid phone column |
| `2026_10_10_000001_create_referral_system.php` | Referral tables/fields |
| `2026_10_10_000002_create_push_subscriptions_table.php` | Push subscriptions |
| `2026_10_11_000001_add_public_enabled_to_push_subscriptions_table.php` | Incremental push visibility preference |
| `2026_10_11_000002_extend_pages_for_navigation_and_seo.php` | Incremental CMS navigation/SEO fields |
| `2026_10_11_000004_create_community_posts_and_replies.php` | Community tables |
| `2026_10_12_000001_create_support_chat_tables.php` | Support conversations/messages and unique user conversation key |
| `2026_10_12_000002_add_platform_to_faqs_table.php` | Incremental FAQ platform column/index |
| `2026_10_12_000003_add_site_key_to_domain_owned_records.php` | Site isolation column across domain-owned tables |
| `2026_10_12_000004_make_unique_keys_site_scoped.php` | Replaces global unique keys with site-scoped keys |
| `2026_10_12_000005_add_admin_push_subscriptions.php` | Incremental admin ownership/FK/index on push subscriptions |
| `2026_10_23_000001_enforce_notification_retention_limit.php` | Data-retention operation plus notification retrieval index |
| `2026_10_23_000002_add_user_notification_preference.php` | Incremental user notification preference |
| `2026_10_23_000003_add_gateway_payment_url.php` | Incremental wallet-request gateway URL |
| `2026_10_24_000001_add_admin_credit_audit_to_transactions_table.php` | Incremental ledger audit/idempotency columns |
| `2026_11_03_000001_create_admin_payment_alerts_tables.php` | Payment alert and per-admin read tables |
| `2026_11_03_000002_add_user_presence_tracking.php` | User live-status fields and multi-session presence table |
| `2026_11_03_000003_create_support_conversation_admin_reads.php` | Per-admin conversation read markers |
| `2026_11_03_000004_expand_admin_payment_alert_diagnostics.php` | Incremental payment alert diagnostics |

## Consolidation candidates (not deleted)

The following are additive migrations that could be folded into their original table definitions **only in a controlled baseline rebuild / pre-deployment environment**:

- Bid phone field.
- Push subscription public-enabled and admin ownership fields.
- CMS navigation and SEO fields.
- FAQ platform field.
- User notification preference.
- Wallet request gateway payment URL.
- Transaction admin-credit audit fields.
- User live-status columns and presence-session table.
- Payment alert diagnostic fields.

The site-key migration and site-scoped unique-key migration are not simple column additions. They affect multiple tables, existing unique constraints and multi-tenant data semantics; they must remain separately ordered unless a complete fresh-schema baseline is generated and verified.

The notification-retention migration deletes older notification rows during `up()`; it is a data migration, not a redundant schema-only migration, and must not be merged/deleted casually.

## Verification still required before any consolidation

1. Capture `php artisan migrate:status` from each production environment.
2. Capture the actual MySQL schema, indexes and foreign keys from each site/tenant database.
3. Compare the schema against a fresh migration from an empty database.
4. Confirm every incremental migration has a corresponding field/constraint in the consolidated baseline.
5. Validate site-scoped unique constraints and existing duplicate data.
6. Test fresh install, upgrade from a production snapshot, and rollback on a disposable clone.
7. Keep the production migration ledger intact; never run destructive rollback against live wallet, bid, payment, user or chat data.

No production migration or destructive schema operation was run for this task.
