# TODO

- [x] 1. Refactor (see docs/code-smells.md)
- [ ] 2. Seat SQL
  - Booking totals still come from the `trg_bookingseat_after_insert` trigger. Either keep it or compute the price in PHP, but not both (it would double-count).
- [ ] 3. Password reset flow (accounts whose password contained `& < > " '` can't log in; see docs/schema-drift.md)
- [ ] 4. Move login throttling from the session to a table keyed by IP/email
- [ ] 5. Clean up HTML-encoded rows left by the old save-time encoding (see docs/schema-drift.md)
- [ ] 6. Run `composer install` in deploy.yml so `vendor/` is deployed
