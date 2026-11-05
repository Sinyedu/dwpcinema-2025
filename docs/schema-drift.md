# Schema notes: where the code and `SQL/createDB.sql` disagreed

`SQL/createDB.sql` had drifted from what the old application code queried. Where the old code disagreed with
itself, the refactor follows the schema file. The Docker stack builds its database from the schema file plus
`SQL/migrations/`, so a fresh local database matches the code.

| Table | Old code used | Schema file has | What the refactor does |
| --- | --- | --- | --- |
| `SupportTicket` | both `status` *and* `ticketStatus` | `status` | Uses `status` only. |
| `SupportMessage` | `isRead` | *(missing)* | Added by `SQL/migrations/001_support_message_is_read.sql`, which Docker applies automatically. |
| `ContactForm` | `created_at`, `status`, `subject` | `createdAt`, `submittedAt` | The only readers of those columns (`getReservations`) were dead code and were removed. Inserts use columns that exist. |

If you point the app at an older database instead of the Docker one, run the migration in `SQL/migrations/` first.

## Old rows may be HTML-encoded

The old `SecurityController::sanitizeInput()` ran `htmlspecialchars()` **before saving**, so rows written by the old code
(names, news, tournaments, support messages) contain entities such as `&amp;` and `&#039;`. Output is now escaped once, at
render time, so those old rows would show the literal entity text. Rows written by the new code are correct, and a fresh
Docker database is unaffected.

## Passwords with special characters

Registration used to encode the password before hashing, but login checked the raw password. So any account whose
password contained `& < > " '` could never log in (an existing bug, not something this change introduces). Registration now
hashes the password exactly as typed. There is no password-reset flow yet.
