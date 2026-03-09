# PHPUnit Testing Commands - February 5, 2026

## Actual Commands Executed

### Run PHPUnit tests and extract summary

```bash
vendor/bin/phpunit tests/Unit/app/Http/Controllers/activity/ tests/Unit/app/Http/Controllers/auth/ 2>&1 | grep -E "Tests:|OK"
```

Working directory: `/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel`
Exit code: 0

### PHP syntax check on test files

```bash
find tests/Unit/app/Http/Controllers/activity tests/Unit/app/Http/Controllers/auth -name "*Test.php" -exec php -l {} \; 2>&1 | grep -v "^No syntax" | head -20
```

Working directory: `/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel`
Exit code: 0
