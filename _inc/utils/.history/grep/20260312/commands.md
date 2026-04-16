# Grep Commands — 2026-03-12

## PHPUnit debug test filtering

- `timeout 60 php -d memory_limit=512M vendor/bin/phpunit --no-coverage tests/Feature/Debug500Test.php 2>&1 | grep -E '1\)|2\)|3\)|4\)|Exception|Error' | head -20`: filter test errors
- `timeout 60 php -d memory_limit=512M vendor/bin/phpunit --no-coverage tests/Feature/Debug500Test.php 2>&1 | grep -i 'proposal\|status\|Tests:' | head -10`: filter proposal status
- `timeout 60 php -d memory_limit=512M vendor/bin/phpunit --no-coverage tests/Feature/Debug500Test.php 2>&1 | grep -i 'proposal\|status\|Tests:|Error' | head -10`: filter proposal+error
- `timeout 60 php -d memory_limit=512M vendor/bin/phpunit --no-coverage tests/Feature/Debug500Test.php 2>&1 | grep -E 'HANDLER|FINAL|STATUS|Tests:' | head -10`: handler status
- `timeout 60 php -d memory_limit=512M vendor/bin/phpunit --no-coverage tests/Feature/Debug500Test.php 2>&1 | grep -i 'Handler::render\|proposal\|Tests:' | head -10`: handler render
