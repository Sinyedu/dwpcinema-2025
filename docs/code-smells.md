# Code smells: what was found, what changed, what is left

Smell names follow Fowler's *Refactoring*. "Fixed" means fixed in this PR; "Open" means deliberately left for a decision.

## Fixed

| Smell | Where it was | What changed |
| --- | --- | --- |
| **Middle Man** | 15 classes in `controllers/` that only forwarded calls to models (`NewsController`, `GameController`, `BookingController`, ...) | Removed; pages call the models directly. `GameController` was really a model, so it became `models/Game.php`. |
| **Duplicated Code** | Three separate support-ticket controller/model pairs (`SupportController`, `UserSupportController`, `AdminSupportController`, `SupportTicket`, `SupportMessage`) | One `SupportDesk` (rules) over one `SupportModel` (SQL). |
| **Duplicated Code** | Login written three times (`login.php`, `admin_login.php`, `Admin::login`); logout written twice; CSRF written three different ways (`Session`, `includes/csrf.php`, inline) | `LoginService`, one `logout.php`, one `Csrf` class. |
| **Duplicated Code** | Tournament image lookup copy-pasted in `index.php` and `tournaments.php`; opening-hours and locations queries repeated in pages | `tournament_image()` helper; footer and pages use the models. |
| **Duplicated Code** | Admin `<head>` + Tailwind block in 9 views; `trim((string)($_POST[..] ?? ''))` dozens of times; the AJAX header check twice | `includes/head.php`, `post_str()`, `is_ajax()`. |
| **Divergent Change / Feature Envy** | Pages held SQL, validation and rendering together (`user_profile.php`, `booking_confirm.php`, `view_showing.php`) | Rules moved to `Booking`, `SeatRules`, `UserInput`; pages only wire request to response. |
| **Primitive Obsession** | Seat rules as inline loops over strings; password and name checks scattered | `SeatRules::violation()`, `UserInput::registration()/profile()`. |
| **Mysterious / dead code** | `booking.php` (undefined `$totalAmount`, wrong include paths), `ticket.php`, `user_account.php`, `router/router.php`, `contact.js`, `SecurityController` | Deleted. |
| **Data-corrupting design** | `sanitizeInput()` HTML-encoded before saving (names, messages, even passwords) | Store raw; escape with `e()` on output. |
| **Shotgun Surgery** | Changing "who may see a ticket" meant editing three controllers and two JS files | `SupportDesk` is the one place. |
| **Speculative / broken** | `getAllTicketsWithDetails()` and `getReservationInfo()` queried columns that don't exist | Removed. |
| **SQL outside models** | `ReservationService` ran raw queries | Moved into `User` and `Tournament` model methods. |

## Open (left on purpose; see the PR comments)

- **Duplicated Code**: the `if (is_post()) { require_csrf(); ...; redirect(...); }` shape repeats in the admin views. A small "admin CRUD" helper could own it, but it would be a new abstraction worth agreeing first.
- **Admin pages aren't thin**: `views/news.php` and `views/tournaments.php` validate input inline. Validation like `UserInput` for news/tournaments would move that out.
- **Primitive Obsession**: ticket status and priority are strings (`SupportDesk::STATUSES`). An enum would make illegal values unrepresentable.
- **Inconsistent error style**: `ReservationService::submit()` returns `['success','error']`; the other services throw typed exceptions or return `?string`.
- **Divergent Change**: `includes/bootstrap.php` does sessions, headers, errors, URLs, flash messages, JSON and guards.
- **Hidden global state**: the bootstrap helpers reach `$auth` and `$csrf` through `global`.
- **Mysterious Name**: `SupportModel` covers tickets *and* messages.
- **Seat price in a DB trigger**: business logic hidden in `SQL/createDB.sql` (the original TODO's "Seat SQL" item).
