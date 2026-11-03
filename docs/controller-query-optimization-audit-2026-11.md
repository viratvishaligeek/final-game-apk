# Controller and query optimization audit — 3 November 2026

## Changes implemented in this branch

### BiddingDeskController and BidAnalyticsService

- The HTTP controller now validates request inputs, resolves the selected game/business date, and delegates bid aggregation to BidAnalyticsService.
- Jodi, Haruf and Crossing aggregation are separate queries grouped by number. The query does not join wallet transactions or duplicate bid rows.
- The number detail query paginates distinct matching users, then loads their matching bids and same-day/same-game/same-type number totals in bulk.
- Linked transaction IDs are resolved in one query through the actual nullable transactions.bid_id relationship. No transaction ID is inferred from order_no.
- The legacy jodi_crossing response key is retained for older admin scripts.
- The user detail modal paginates by user, so one user is not split across pages.

### UserController

- The initial Show User page no longer loads grouped bid slips and all of their individual bid relationships.
- The page now loads one aggregate per game and requests individual bid rows only when an admin opens that game's modal.
- The bid detail endpoint is paginated and caps page size at 100.
- Wallet transaction and accounting logic was not modified.

### SupportChatController and SupportMessageCreated

- All-user search returns a maximum of 50 users and performs one bulk conversation lookup with per-admin unread counts.
- Conversation initiation locks the user row inside a transaction and reuses the existing unique conversation row.
- Admin-originated message persistence is transactional and dispatches the existing broadcast event after commit.
- The event loads its message and conversation relations in bulk and broadcasts to the admin support channel and the target user's private channel.
- User-controlled message content remains escaped by the existing view rendering paths; the new search results use textContent rather than interpolating user data into HTML.

## Query and index observations

- The bids migration already provides game/date/type/number/status and user/date indexes. No additional index was added without a production MySQL EXPLAIN plan.
- Number distribution uses grouped aggregate queries rather than one query per number.
- User number details use a bounded set of matching user IDs and one grouped query for all of those users.
- Admin user search is bounded to 50 rows and does not query conversations once per user.
- The existing unique support_conversations.user_id constraint is the final duplicate-prevention guard. User-row locking serializes concurrent conversation creation attempts on transactional databases.
- The existing per-admin read marker remains the source of unread state. Search and global badge refreshes compute the exact count instead of incrementing from both HTTP and Pusher events.
- The global conversations response now includes `meta.total_unread_count`, calculated across all site-scoped user messages with a left-joined per-admin read marker. The 500-row inbox list limit therefore does not truncate the header badge total.

## Deliberately not refactored

Wallet debit/credit, payment confirmation, winning settlement, and game closure controllers were not broadly rewritten. These paths affect balances and accounting direction; a safe refactor requires a separate end-to-end ledger audit and idempotency tests. The current change leaves their business rules and transaction ordering untouched.

The remaining large controllers should be split incrementally after behavior-specific tests exist. This branch does not claim that every controller in the repository has been refactored.
