# Admin route audit

## Route organization

Admin routes are organized in `routes/admin.php` under the stable parent group:

- URI prefix: `/admin`
- route-name prefix: `admin.`
- middleware: `auth:admin`, `admin.site`

Nested feature groups cover dashboard/profile, payment alerts, support chat, community moderation, push notifications, games/results/winners, bidding desk, users/members/roles, wallet requests/adjustments, CMS, and mobile-app configuration.

## Contract preservation

The route refactor preserves existing endpoint URIs and names, including:
- `admin.support-chat.*`
- `admin.users.bids`
- `admin.wallet.*`
- `admin.results.*`
- `admin.bidding-desk.*`
- Existing resource route names for games, users, members, roles, pages, home-page, FAQs, banners, settings, and winners.

A feature regression test asserts representative route names and verifies that each has both `auth:admin` and `admin.site` middleware.

## Other route files

- `routes/api.php`: player API and Sanctum broadcast authorization.
- `routes/channels.php`: player-private channels and the new per-user presence channel with `sanctum` / `admin` guards; per-user authorization is delegated to `UserPresenceChannelAuthorizer`.
- `routes/community.php`: community endpoints.
- `routes/extra.php`: non-admin utility/maintenance routes.

## Outstanding security review

`routes/extra.php` contains a public cache/optimization clear endpoint. Its operational consumers are not documented in the repository. It has not been moved behind Admin authentication because that could break a deployment or hosting maintenance workflow; confirm its intended caller before changing its access contract. It should not remain publicly callable without an explicit decision about authentication or a deployment secret.

## Verification

The backend workflow runs PHP syntax checks for `routes/admin.php` and `routes/channels.php`, and the focused feature test verifies the representative Admin route contract. A full `route:list` diff against production was not available; production route consumers/bookmarks remain unverified.
