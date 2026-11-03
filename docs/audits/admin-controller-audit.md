# Admin controller audit and refactoring plan

Audit date: 2026-11-04. All 22 controllers under `app/Http/Controllers/Backend/` were inventoried. This is a source review; it is not a claim that every controller has been fully refactored.

## Extracted services in the current implementation

| Controller responsibility | Extracted service | Status |
|---|---|---|
| User → Game Activity submission grouping | `App\Services\Admin\UserBidActivityService` | Merged in PR #58; controller delegates grouping and legacy identifier isolation. |
| Result settlement and reversal | `App\Services\Admin\GameResultSettlementService` | Merged in PR #58; settlement operations moved out of ResultController. |
| Admin wallet credit idempotency / retry handling | `App\Services\Admin\AdminWalletCreditService` | Added in this follow-up; regression test covers replay and conflicting key reuse. |
| Per-user presence channel authorization | `App\Services\Presence\UserPresenceChannelAuthorizer` | Added in this follow-up; player ownership and Admin site scoping are testable without mocking Pusher internals. |

## Controller inventory

| Controller | Main responsibility | Review result / remaining work |
|---|---|---|
| `AdminPaymentAlertController` | Pending payment alerts and acknowledgement | Focused endpoint controller; verify policy/tenant middleware in integration tests. |
| `AdminPushSubscriptionController` | Admin push subscription lifecycle | Small endpoint controller; no extraction currently required. |
| `AuthController` | Admin login and password recovery | Review session rotation, throttling and recovery token lifecycle separately. |
| `BannerController` | Banner CRUD | Conventional CRUD; no oversized workflow identified. |
| `CommunityModerationController` | Post/reply moderation and policy | Review moderation policy and transaction boundaries; further extraction pending. |
| `DashboardController` | Dashboard aggregates and user reports | Oversized aggregate/report queries remain candidates for query services. |
| `FaqController` | FAQ CRUD | Conventional CRUD; no oversized workflow identified. |
| `Game\BiddingDeskController` | Bidding summaries, details and profit/loss | Profit/loss and detail aggregation remain candidates for query services; not fully extracted. |
| `Game\GameController` | Game CRUD | Conventional CRUD; no oversized workflow identified. |
| `Game\ResultController` | Result entry, notifications, settlement/reversal | Settlement/reversal extracted; result write validation and notification orchestration still need review. |
| `Game\WinnerController` | Winner CRUD/listing | Winner listing and notification-related logic remain candidates for extraction. |
| `HomePageController` | Homepage section CRUD | Review validation and ordering logic; no service extraction yet. |
| `MobileAppController` | App limits, notices and marquee settings | File/config update workflows remain candidates for extraction. |
| `PagesController` | CMS page CRUD | Conventional CRUD; no oversized workflow identified. |
| `PushNotificationController` | Admin notification listing/broadcast | Review broadcast fan-out and rate limiting; no extraction yet. |
| `SettingController` | Site settings and uploaded assets | Store/update workflow remains an oversized candidate; extraction pending. |
| `SupportChatController` | Conversations, search, messages, media | Query and send workflows remain candidates for a support conversation service; tenant isolation is covered by existing tests. |
| `User\MemberController` | Admin member CRUD | Conventional CRUD; no oversized workflow identified. |
| `User\RoleController` | Roles and permissions | Review permission synchronization; no extraction yet. |
| `User\UserController` | User CRUD, activity and bid details | Bid grouping extracted; profile/update and related report workflows remain in controller. |
| `User\WalletController` | Admin credit/debit | Credit idempotency extracted; debit workflow remains in controller. |
| `User\WalletRequestController` | Top-up/withdrawal approval and rejection | Approval/rejection transactions and notifications remain candidates for a dedicated service. |

## Priority remaining refactors

1. Extract wallet request approve/reject workflows with transaction, ledger, and notification regression tests.
2. Extract remaining BiddingDesk detail aggregation; profit/loss report aggregation is now in a service.
3. Extract Dashboard report aggregation; preserve date boundaries, site scoping, and summary values.
4. Extract SettingController file/config persistence with rollback-safe storage handling.
5. Extract SupportChat conversation query/send orchestration while preserving per-admin unread counts and site scoping.
6. Review MobileAppController and WinnerController workflows after their existing validation and side-effect contracts are captured in tests.

## Safety / verification

- No controller was rewritten wholesale.
- Service extraction preserves existing controller route contracts.
- The focused workflow lints the extracted services/controllers and runs the Admin feature regression file.
- Full controller refactoring is **not complete**. The list above records the remaining candidates instead of claiming every controller has been refactored.
