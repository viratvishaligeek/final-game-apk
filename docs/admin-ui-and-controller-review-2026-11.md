# Admin architecture review — November 2026

## Repository boundary

This review and branch are confined to `viratvishaligeek/final-game-apk`. No Vue repository files were changed.

## User Game Activity implementation

- The former User Show query grouped bid rows by `game_id`. That merged multiple separately submitted betting activities.
- The submission identifier is the existing `bids.order_no`; the new summary groups by this identifier only.
- The query aggregates count, sum, representative bid and latest bid using one grouped query, then eager-loads representative games in one additional query.
- Detail retrieval is constrained by both the route-bound user and `order_no`, so a guessed order number cannot disclose another user's bids.
- The modal now renders individual responsive bid cards and badges rather than a table.
- New submissions now receive a ULID-based order number. The former seconds-only timestamp could collide for two independent requests within one second.

## Admin Live Chat

- The existing all-registered-user search endpoint and idempotent conversation start endpoint were already present and remain in use.
- Search remains independent of whether a conversation exists; existing unique conversation ownership and transaction locking are preserved.
- The Admin Live Chat page now has a responsive split-pane layout, WhatsApp-inspired incoming/outgoing bubbles, compact mobile sizing and jQuery AJAX/event handling.
- Dynamic names and message content continue to be escaped/text-rendered before insertion.
- Shared inbox snapshots now dispatch from the conversations response, not the payment-alert pending response.

## Blade JavaScript migration status

jQuery is loaded once in the shared Admin layout. Custom event/AJAX code was migrated in:

- `backend/users/show.blade.php`
- `backend/users/index.blade.php`
- `backend/support-chat/index.blade.php`
- `backend/games/index.blade.php`
- `backend/banner/index.blade.php`
- `backend/faqs/index.blade.php`
- `backend/home-page/index.blade.php`
- `backend/member/index.blade.php`
- `backend/pages/index.blade.php`
- `backend/roles/edit.blade.php`
- `backend/roles/index.blade.php`
- `backend/wallet/request-add.blade.php`
- `backend/wallet/request-withdraw.blade.php`
- `backend/result/index.blade.php`

This is not a completed conversion of every Admin Blade view. Remaining custom scripts include the shared notification/payment-alert manager, bidding desk, dashboard and other page-specific integrations. Plugin initializers and browser APIs (MediaRecorder, Firebase Messaging, Pusher) remain native where jQuery does not provide a replacement. A repository-wide conversion still requires page-by-page regression testing and is not represented as complete.

## Controller inventory and refactoring decision

All 22 Backend controller files were inventoried. The main complexity hotspots include:

- `Game/ResultController.php` — approximately 523 lines; contains result publication, settlement, reversal and notification workflows. It was not mechanically split because changing settlement boundaries without dedicated financial regression tests is unsafe.
- `User/WalletRequestController.php` — approximately 274 lines; approval/rejection and wallet/push side effects.
- `User/UserController.php` — approximately 270 lines before this change; submission aggregation has been extracted to `Services/Admin/UserBidActivityService.php`.
- `DashboardController.php` — approximately 260 lines; reporting and profile actions.
- `Game/BiddingDeskController.php` — approximately 228 lines; aggregation/reporting.
- `User/WalletController.php` — approximately 219 lines; wallet credits/debits.
- `MobileAppController.php` — approximately 208 lines; settings and barcode storage.
- `SupportChatController.php` — approximately 300 lines; search, conversation creation, inbox, message history, media and send operations.

Short CRUD controllers (Banner, FAQ, Game, HomePage, Member, Role, Winner, PushNotification, AdminPaymentAlert, AdminPushSubscription, CommunityModeration and Pages) were also included in the inventory. They were not rewritten solely to reduce line count.

A broader extraction of result settlement, wallet and payment workflows is deliberately not attempted in this UI-focused branch: it requires dedicated transaction, rollback and idempotency tests for each extracted service.

## Admin route review

`routes/admin.php` already has one outer `admin` prefix/name group protected by `auth:admin` and `admin.site`, plus module-specific controller groups for dashboard, wallet, results, bidding desk and mobile settings. User bid details are declared before the users resource route. No duplicate Admin route definition was found in the reviewed file. Route names and middleware were left unchanged.

## Presence / HTTP 429 server-side review

- The player presence heartbeat route is `POST /api/v1/presence/heartbeat` with `throttle:30,1`.
- The presence disconnect route is `DELETE /api/v1/presence/session` with `throttle:10,1`.
- `PresenceController::heartbeat()` documents a five-second client heartbeat but persists the session/user last-seen values at most every 15 seconds.
- A single five-second client loop is about 12 calls/minute and is below the route's 30/minute limit. Duplicate client timers, multiple app contexts or retry loops could exceed it, but the Vue client source and live Network traces are outside the explicitly authorized Admin-only repository boundary.
- The heartbeat endpoint also maintains multi-device/session leases and last-seen state. It is not safe to delete it or claim Pusher presence fully replaces it without auditing all clients and the reconnect/expiry contract.
- Throttle limits were not increased. No presence route/controller was deleted. No claim is made that client-side 429s are resolved.
