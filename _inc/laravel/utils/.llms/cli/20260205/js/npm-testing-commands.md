# NPM Testing Commands - February 5, 2026

## Actual Commands Executed

### Working Directory

```bash
cd /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/tests/frontend/js
```

### Run all tests silently

```bash
npm test -- --silent 2>&1
```

### Run tests and show last 20 lines

```bash
npm test -- --silent 2>&1 | tail -20
```

### Run tests and show last 30 lines

```bash
npm test -- --silent 2>&1 | tail -30
```

### Run tests and show first 50 lines

```bash
npm test -- --silent 2>&1 | head -50
```

### Run tests with coverage summary

```bash
npm test -- --coverage --coverageReporters=text-summary --silent 2>&1 | tail -20
```

### Run tests verbosely and filter status

```bash
npm test -- --verbose 2>&1 | grep -E "PASS|FAIL|Tests:" | head -10
```

## Results

- **Test Suites**: 4 passed (erp-guard, erp-utils, route-guard, checkMounted)
- **Total Tests**: 354 passing
- **Time**: ~3-5 seconds per run
