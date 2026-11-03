# Database migration source audit — 3 November 2026

## Scope and limitation

This audit covers every migration file present on the main branch at the time of review: **37 migration files**. It is a source-code audit, not a live database audit. No production database credentials or schema dump were available, so the audit cannot determine which migration rows have actually been applied in production. Run php artisan migrate:status against a protected staging/production-like connection before deployment.

No migration file was deleted, rewritten, consolidated, or executed as part of this enhancement.

## Inventory

### Initial schema and core application tables

| Migration | Tables / changes | Classification |
|---|---|---|
| 0001_01_01_000000_create_admins_table.php | admins, password_reset_tokens | Initial schema |
| 0001_01_01_000002_create_jobs_table.php | jobs, job_batches, failed_jobs | Initial queue schema |
| 2026_09_21_135241_games.php | games | Initial schema |
| 2026_09_21_194507_pages.php | pages | Initial schema |
| 2026_09_21_194555_users.php | users | Initial schema |
| 2026_09_23_073128_create_personal_access_tokens_table.php | personal_access_tokens | Sanctum schema |
| 2026_09_23_093107_password_reset_otps.php | password_reset_otps | Feature table |
| 2026_09_25_192739_create_permission_tables.php | Spatie permissions, roles and pivot tables | Package schema |
| 2026_09_25_202529_faqs.php | faqs | Feature table |
| 2026_09_25_210547_create_results_table.php | results | Feature table |
| 2026_09_26_110058_create_banners.php | banners | Feature table |
| 2026_09_28_001826_bids.php | bids | Core betting table |
| 2026_09_28_085439_create_notifications_table.php | notifications | Feature table |
| 2026_09_28_085557_create_transactions_table.php | transactions | Financial ledger |
| 2026_09_28_113346_wallet_requests.php | wallet_requests | Financial workflow |
| 2026_09_29_161403_game_winners.php | winners | Settlement table |
| 2026_10_07_174850_home_page.php | home_pages | CMS table |
| 2026_10_07_205935_settings.php | settings | Configuration table |

### Incremental feature and compatibility migrations

| Migration | Tables / changes | Classification |
|---|---|---|
| 2026_10_09_170000_add_phone_to_bids_table.php | Adds nullable bids.phone | Incremental column |
| 2026_10_10_000001_create_referral_system.php | Adds referral columns to users; creates referral_rewards; backfills referral data | Incremental feature |
| 2026_10_10_000002_create_push_subscriptions_table.php | Creates push_subscriptions | Feature table |
| 2026_10_11_000001_add_public_enabled_to_push_subscriptions_table.php | Adds push_subscriptions.public_enabled | Incremental column |
| 2026_10_11_000002_extend_pages_for_navigation_and_seo.php | Adds menu and SEO columns to pages | Incremental columns |
| 2026_10_11_000004_create_community_posts_and_replies.php | Creates community_posts, community_replies | Feature tables |
| 2026_10_12_000001_create_support_chat_tables.php | Creates support_conversations, support_messages | Feature tables |
| 2026_10_12_000002_add_platform_to_faqs_table.php | Adds faqs.platform | Incremental column |
| 2026_10_12_000003_add_site_key_to_domain_owned_records.php | Adds site_key to site-owned tables | Multi-site compatibility |
| 2026_10_12_000004_make_unique_keys_site_scoped.php | Changes uniqueness for users, settings, push_subscriptions to include site_key | Multi-site constraint update |
| 2026_10_12_000005_add_admin_push_subscriptions.php | Adds nullable push_subscriptions.admin_id and index | Incremental column / FK |
| 2026_10_23_000001_enforce_notification_retention_limit.php | Adds notifications_user_created_id_idx | Incremental index |
| 2026_10_23_000002_add_user_notification_preference.php | Adds users.notifications_enabled | Incremental column |
| 2026_10_23_000003_add_gateway_payment_url.php | Adds wallet_requests.gateway_payment_url | Incremental column |
| 2026_10_24_000001_add_admin_credit_audit_to_transactions_table.php | Adds transaction audit balance, admin attribution and idempotency key | Financial audit columns |
| 2026_11_03_000001_create_admin_payment_alerts_tables.php | Creates admin_payment_alerts, admin_payment_alert_reads | Feature tables |
| 2026_11_03_000002_add_user_presence_tracking.php | Adds user live status and creates user_presence_sessions | Incremental column / feature table |
| 2026_11_03_000003_create_support_conversation_admin_reads.php | Creates support_conversation_admin_reads | Per-admin read-state table |
| 2026_11_03_000004_expand_admin_payment_alert_diagnostics.php | Alters admin_payment_alerts with attempt correlation and diagnostic fields | Incremental columns / index / unique key |

## Findings

1. **No duplicate Schema::create() of the same application table was found** in the 37-file source inventory. Later migrations that touch existing tables are intentional incremental changes, not duplicate table creation.
2. support_conversations and support_messages are created once. support_conversation_admin_reads is a separate table for per-admin read state.
3. admin_payment_alerts is created once and later extended by 2026_11_03_000004_expand_admin_payment_alert_diagnostics.php. This is legitimate schema evolution.
4. users, settings, and push_subscriptions receive site-scoped unique constraints in 2026_10_12_000004_make_unique_keys_site_scoped.php. Do not collapse that migration into the original create-table migrations after deployment.
5. bids has indexes for order lookup, user/date history, game/date/status, and game/date/type/number/status. The latter supports number-wise aggregation filters. Do not add another overlapping bid index without checking MySQL EXPLAIN output and write overhead.
6. notifications has the original user_id index and later receives (user_id, created_at, id). The single-column index is a **candidate** for redundancy because it is a left-prefix of the composite index. Confirm with SHOW INDEX and query plans before any forward-only drop migration.
7. Some site_key and composite indexes may overlap by left-prefix. They are candidates for measured optimization only; source code alone is not enough to justify dropping them.
8. Foreign-key cascade behavior is present on core bid, transaction, wallet, chat and alert relationships. Any future FK change must be tested against existing production data.
9. Rollback methods exist in the inspected files, but a successful rollback cannot be asserted without executing against a disposable database. Backfill migrations and type changes need special staging review.

## Deployment classification

- **Historical / preserve:** all 37 files above until migrate:status and the production migration table prove otherwise.
- **Pending / never deployed:** cannot be classified from GitHub source alone.
- **New development migrations for this feature:** none required by the current implementation. Existing unique support_conversations.user_id prevents duplicate conversation rows; existing bid columns and indexes support the dashboard.
- **Consolidation:** none recommended for deployed history. Do not rewrite historical migration files.

## Required staging verification

1. Take a restorable database snapshot.
2. Run php artisan migrate:status.
3. Compare SHOW CREATE TABLE and SHOW INDEX for bids, notifications, support_conversations, support_messages, support_conversation_admin_reads, admin_payment_alerts, and admin_payment_alert_reads.
4. Run php artisan migrate --pretend first, then apply only pending migrations on staging.
5. Run the full test suite and verify FK integrity.
6. Test rollback only on a disposable database copy.
7. Do not run destructive migration cleanup or migrate:fresh against production.
