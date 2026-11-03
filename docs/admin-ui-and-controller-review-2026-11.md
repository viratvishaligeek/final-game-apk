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

jQuery is loaded once in the shared Admin layout. All 47 Blade files under `resources/views/backend` were inventoried. The migration removed inline event attributes, `addEventListener` registrations and Fetch API calls from the Admin Blade views. Relevant page behavior now uses jQuery ready handlers, delegated `.on()` events, jQuery DOM helpers and `$.ajax()` where applicable.

This includes the shared payment/chat notification manager, site selector, dashboard, bidding desk, User Show/User List, Live Chat, wallet request pages, result controls and CRUD confirmation/preview pages. Native browser/platform APIs remain where jQuery is not a substitute (Pusher connection bindings, Firebase Messaging, Notification, FileReader, MediaRecorder and Bootstrap's programmatic modal API). External plugin scripts and the shared `public/backend/js/scripts.js` asset are not rewritten by this Blade-only migration.

The current CI compiles all Blade views. Browser-level interaction testing is still required before treating this migration as production-verified.

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
