# Share-link password base64→bcrypt migration — 2026-05-07

## Investigation

```bash
grep -rln 'share\|shared_link\|shareLink' app/Http/Controllers/ --include='*.php'
# → ProjectController.php (Contact/MessagesController unrelated)

grep -rn 'invoiceLink' app/Http/Controllers/ --include='*.php'
# → BillController, InvoiceController, ProposalController — none have password gates

grep -rn 'Hash::check\|password_protected' app/Http/Controllers/ --include='*.php'
# → only ProjectController.php:1825-1827 has a project-link password gate
```

Single gate confirmed at `app/Http/Controllers/Planning/ProjectController.php`
function `projectLink()` lines 1823-1834.

## Fix

Inline base64 fallback before the `Hash::check` rejection:

```php
$ok = Hash::check($entered, $stored);
if (!$ok && $stored !== '' && !preg_match('/^\$2[ayb]\$/', $stored)) {
    $decoded = base64_decode($stored, true); // strict
    if ($decoded !== false && hash_equals($decoded, $entered)) {
        $project->password = Hash::make($entered);
        $project->saveQuietly();
        $ok = true;
    }
}
```

- `preg_match('/^\$2[ayb]\$/', ...)` skips the legacy path for any value
  already in bcrypt format ($2a$/$2b$/$2y$).
- `base64_decode($x, true)` strict-mode returns `false` on non-base64 data
  (avoids false positives on garbage strings).
- `hash_equals` for constant-time comparison.
- `saveQuietly()` re-hashes once and persists without firing model events.

## Verification

```bash
php -l app/Http/Controllers/Planning/ProjectController.php
# → No syntax errors

vendor/bin/phpunit --filter='projectLink|PRJ_LNK' tests/Unit/app/Http/Controllers/planning/ProjectControllerTest.php
# → 7/7 OK

vendor/bin/phpstan analyse app/Http/Controllers/Planning/ProjectController.php --level=3
# → No errors
```

Logic smoke (standalone PHP):

```
stored=c2VjcmV0MTIz (base64 of secret123)  | matches bcrypt prefix=no | decodes=secret123 | hash_equals=yes
stored=not-base64-or-hash                  | base64_decode strict → false (skip)
stored=$2y$12$...                          | matches bcrypt prefix=yes (Hash::check handles it directly)
```

## Out-of-scope follow-up
- A focused Feature test (real DB, real Project record with a base64 password)
  would lock this migration path against future regressions. Existing
  ProjectControllerTest tests are smoke-only.
