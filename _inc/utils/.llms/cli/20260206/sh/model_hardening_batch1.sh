# Model Hardening CLI Commands - Batch 1 (Abstracts + Activity + Bills)
# Date: 2026-02-06

# --- Phase 1: Mechanical Hardening ---
# Python script that sorts imports (with {} spreading), removes comments,
# normalizes newlines, enforces backslash exceptions, removes trailing whitespace
python3 /tmp/harden_models.py
# Script targets: app/Models/Abstracts, app/Models/Activity, app/Models/Bills
# Result: 83/86 files modified

# --- Phase 2: Deep Semantic Hardening ---
# Python script that wraps unprotected methods in try/catch with Log::error,
# ensures Log facade import, removes debug output (echo, var_dump, dd, etc.)
python3 /tmp/harden_deep_v2.py
# Result: 55/86 files modified

# --- Verification ---
# Syntax check all files
find app/Models/Abstracts app/Models/Activity app/Models/Bills -name '*.php' -exec php -l {} \; 2>&1 | grep -v "No syntax errors"

# PHPStan analysis
php vendor/bin/phpstan analyse app/Models/Abstracts/ app/Models/Activity/ app/Models/Bills/ --level=0 --no-progress --error-format=table

# Run tests
php vendor/bin/phpunit tests/Unit/app/Http/Controllers/ --no-coverage
