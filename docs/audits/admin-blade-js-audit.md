# Admin Blade JavaScript audit

Audit scope: every Blade template under `resources/views/backend/` in the current Admin branch. The inventory contains 48 Blade files. Third-party library internals (Bootstrap, DataTables, SweetAlert, Feather, Pusher, browser audio APIs) are not custom page JavaScript and are not rewritten as jQuery.

## Converted / centralized

| Template / area | Change |
|---|---|
| `include/layout.blade.php` | Admin alert/unread HTTP calls use `$.ajax`; unread dropdown DOM is built with jQuery; sound, custom window events and visibility handlers use jQuery where applicable. Pusher connection lifecycle remains the Pusher API. |
| `include/sidebar.blade.php` | Site selector submission uses jQuery change handler. |
| `bidding-desk.blade.php` | AJAX loading, cancellation, filters, number/haruf grids, detail modal and pagination use jQuery. |
| `users/index.blade.php` | Credit modal, status actions, wallet AJAX, live-presence DOM updates use jQuery. Delete action uses the shared handler. |
| `users/show.blade.php` | User-specific live-presence indicator uses jQuery and the Admin's shared Pusher client. |
| `support-chat/index.blade.php` | Search/conversation list rendering, status, voice controls, message form, and AJAX use jQuery. Browser media recorder and Pusher APIs remain native platform interfaces. |
| `dashboard.blade.php` | DataTable initialization and DOM validation use jQuery. |
| `banner/create.blade.php`, `banner/edit.blade.php` | Image preview event and DOM updates use jQuery. |
| `result/index.blade.php` | Date filter and result-revert confirmation use jQuery. |
| `roles/edit.blade.php` | Permission group/select-all interactions use jQuery. |
| `wallet/request-add.blade.php`, `wallet/request-withdraw.blade.php` | Proof preview, approve confirmation, and reject modal setup use jQuery. |
| `games/index.blade.php`, `banner/index.blade.php`, `faqs/index.blade.php`, `home-page/index.blade.php`, `pages/index.blade.php`, `member/index.blade.php`, `roles/index.blade.php` | Repeated delete-confirmation logic centralized as one delegated jQuery handler in the Admin layout. |

## Reviewed templates with no applicable custom page JavaScript

The following templates were checked for inline event handlers and custom scripts; remaining scripts are either third-party includes, Blade markup, or pre-existing jQuery integrations:

- `auth/layout.blade.php`, `auth/login.blade.php`
- `banner/index.blade.php`, `faqs/create.blade.php`, `faqs/edit.blade.php`
- `games/create.blade.php`, `games/edit.blade.php`
- `home-page/create.blade.php`, `home-page/edit.blade.php`
- `member/create.blade.php`, `member/edit.blade.php`
- `mobile-app/limits.blade.php`, `mobile-app/marque.blade.php`, `mobile-app/notice.blade.php`
- `pages/create.blade.php`, `pages/edit.blade.php`
- `partials/number-card.blade.php`, `partials/toastr.blade.php`
- `profit_loss_history.blade.php`, `profit_loss_transactions.blade.php`
- `profile.blade.php`, `push-notifications.blade.php`
- `roles/create.blade.php`, `roles/index.blade.php`
- `settings.blade.php`
- `users/create.blade.php`, `users/edit.blade.php`
- `winner.blade.php`

## Verification status

- Static inventory covers all 48 files.
- The focused backend workflow includes `php artisan view:cache`, so Blade compilation is checked.
- A full browser interaction pass across every Admin form/table has not been run. Do not treat source conversion alone as browser QA.
- Pusher client and MediaRecorder/Audio APIs remain native because jQuery does not replace those platform/library interfaces.
