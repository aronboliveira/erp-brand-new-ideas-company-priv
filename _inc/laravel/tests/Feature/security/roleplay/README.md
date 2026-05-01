# Security Roleplay Testing Framework

Multi-language security testing framework with role-based actors simulating real-world attack and defense scenarios against the ERP Brand New Ideas Company application.

## Overview

Each **role** represents a security persona with different skill levels, objectives, and toolkits:

| Role            | Skill Level | Languages                   | Scripts | Focus                                 |
| --------------- | ----------- | --------------------------- | ------- | ------------------------------------- |
| **Green Hat**   | Beginner    | JS, Bash, Python, PHP, WASM | 6       | Cookie theft, brute force, spam       |
| **White Hat**   | Advanced    | JS, Bash, Python, PHP, WASM | 10      | OWASP Top 10, CSP, SSRF, crypto       |
| **Black Hat**   | Expert      | JS, Bash, Python, PHP, WASM | 12+     | Evasion, exfiltration, XSS chains     |
| **CISO**        | Executive   | JS, Bash, Python, PHP, WASM | 10      | Compliance, TLS, GDPR/LGPD, policy    |
| **Backend Dev** | Senior      | JS, Bash, Python, PHP, WASM | 7       | SAST, secrets, rate limiting, hashing |
| **QA**          | Mid-Senior  | JS, Bash, Python, PHP, WASM | 8       | Fuzzing, regression, a11y, validation |

## Directory Structure

```
tests/
├── Feature/security/roleplay/
│   ├── {role}/
│   │   ├── js/scripts/          # Node.js CJS scripts
│   │   ├── bash/scripts/        # Shell scripts (curl-based)
│   │   ├── py/scripts/          # Python 3 scripts
│   │   ├── php/scripts/         # PHP CLI scripts
│   │   ├── wasm/scripts/        # WASM loaders (via Node.js)
│   │   ├── php/                 # PHPUnit Feature tests
│   │   └── py/                  # pytest Feature tests
│   └── ...
├── Unit/security/roleplay/
│   ├── {role}/
│   │   ├── js/*.test.cjs        # Jest unit tests
│   │   └── php/*Test.php        # PHPUnit unit tests
│   └── ...
└── mock-apps/                   # Intentionally vulnerable HTML apps
    ├── manifest.json
    ├── vulnerable-form/
    ├── session-test/
    ├── api-test/
    ├── websocket-test/
    └── upload-test/
```

## Running Tests

### Jest (JavaScript unit tests)

```bash
# All roleplay tests
npx jest tests/Unit/security/roleplay --no-coverage

# Specific role
npx jest tests/Unit/security/roleplay/white-hat

# Specific test file
npx jest tests/Unit/security/roleplay/qa/js/qa-new-scripts.test.cjs
```

### PHPUnit (PHP tests)

```bash
php artisan test --filter=security/roleplay
```

### pytest (Python tests)

```bash
cd tests/python && python -m pytest ../Feature/security/roleplay/ -v
```

### Bash scripts (manual execution)

```bash
bash tests/Feature/security/roleplay/white-hat/bash/scripts/owasp_sqli_scan.sh
```

## Script Conventions

1. **Header**: Each script starts with a roleplay banner (`[ROLE] Script Name`)
2. **Pull Request markers**: `// PULL REQUEST START` and `// PULL REQUEST END`
3. **Exports**: JS scripts export functions for unit test validation
4. **CLI mode**: `require.main === module` or `__name__ == "__main__"` for direct execution
5. **Target**: Scripts accept `APP_URL` env var or `$1` argument, default `http://127.0.0.1:8000`

## Security Notes

- **Black-hat scripts** are git-ignored (`**/black-hat/` in `.gitignore`)
- Never commit real credentials or exploit code to the repository
- All scripts target only `localhost` / `127.0.0.1` by default
- Mock-apps are intentionally vulnerable for testing purposes only
