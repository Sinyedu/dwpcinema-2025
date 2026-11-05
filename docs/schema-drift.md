# Schema drift: please verify against production

`SQL/createDB.sql` no longer matches what the application code expected. The refactor standardised on the
schema file where the old code disagreed with itself. **Run these against production and compare before deploying.**

```sql
DESCRIBE SupportTicket;
DESCRIBE SupportMessage;
DESCRIBE ContactForm;
```

| Table | Old code used | Schema file has | What the refactor does |
| --- | --- | --- | --- |
| `SupportTicket` | both `status` *and* `ticketStatus` | `status` | Uses `status` only. If production only has `ticketStatus`, rename it (or tell me and I'll switch the code). If it has **both** columns with different data, reconcile them first or closed tickets may look open. |
| `SupportMessage` | `isRead` | *(missing)* | Needs `SQL/migrations/001_support_message_is_read.sql`. |
| `ContactForm` | `created_at`, `status`, `subject` | `createdAt`, `submittedAt` | The only readers of those columns (`getReservations`) were removed as dead code. Inserts use columns that exist. |

## Existing data is HTML-encoded

The old `SecurityController::sanitizeInput()` ran `htmlspecialchars()` **before saving**, so existing rows for names,
news, tournaments and support messages contain entities such as `&amp;` and `&#039;`. Output is now escaped once, at render
time, so those old rows display the literal entity text (e.g. `Tom &amp; Jerry`). New rows are correct. To clean up old data:

```sql
-- Review first, run on a copy. Example for one column:
UPDATE News SET newsTitle = REPLACE(REPLACE(REPLACE(newsTitle, '&amp;', '&'), '&#039;', ''''), '&quot;', '"');
```

Passwords were also encoded before hashing at registration, but login checked the raw password. So any account whose
password contained `& < > " '` could **never** log in (an existing bug, not something this change introduces). Those users
need a password reset; there is no reset flow yet. Registration now hashes the password exactly as typed.
