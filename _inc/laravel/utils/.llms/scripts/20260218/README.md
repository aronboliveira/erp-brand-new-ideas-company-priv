# Utility / Moment-Routine Scripts — 2026-02-18

Moved from project root and `_inc/laravel/` on 2026-02-18 to declutter the main codebase.

## Files

| File               | Purpose                                                                             | Origin          |
| ------------------ | ----------------------------------------------------------------------------------- | --------------- |
| `sconfig-test.php` | PHP script to dump server config, permissions, and database constants for debugging | `_inc/laravel/` |
| `verification.php` | System verification — checks PHP version, extensions, env, DB connectivity          | `_inc/laravel/` |
| `test_login.sh`    | Bash script to test the login flow via curl (CSRF + POST + session)                 | `_inc/laravel/` |
| `_test.sh`         | Shell one-liners for log processing, file search, and DB log archiving              | project root    |
| `_test.js`         | Browser JS snippet to inspect computed SVG transforms                               | project root    |
| `_test.css`        | CSS test/scratch file (29KB of test styles)                                         | project root    |
| `_test.html`       | HTML test page (67KB — likely a rendered view snapshot for diffing)                 | project root    |
| `_test.sql`        | SQL test queries                                                                    | project root    |
