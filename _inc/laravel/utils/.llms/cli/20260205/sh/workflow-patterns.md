# CLI Workflow Patterns - February 5, 2026

## Actual Multi-Step Commands Executed

### Find testable JavaScript files (exclude vendors)

```bash
find /workspace/erp/_inc/laravel/public/assets/js -name "*.js" -type f -not -path "*/node_modules/*" | grep -E "(core|generic|pages)" | grep -v ".min.js" | head -20
```

**Steps**: find .js files → exclude node_modules → filter core/generic/pages → exclude minified → show first 20

### Find large JavaScript files with sizes

```bash
find /workspace/erp/_inc/laravel/public/assets/js -name "*.js" -type f -size +5k -not -path "*/node_modules/*" | grep -v ".min.js" | xargs ls -lh | awk '{print $5, $9}' | head -20
```

**Steps**: find >5KB files → exclude node_modules → exclude minified → list details → extract size+path → show first 20

### Run tests and show summary

```bash
cd /workspace/erp/_inc/laravel/tests/frontend/js && npm test -- --silent 2>&1 | tail -20
```

**Steps**: change directory → run tests silently → merge stderr to stdout → show last 20 lines

### Run tests and extract status

```bash
npm test -- --verbose 2>&1 | grep -E "PASS|FAIL|Tests:" | head -10
```

Working directory: `/workspace/erp/_inc/laravel/tests/frontend/js`
**Steps**: run verbose tests → merge output → filter status lines → show first 10

### Create multiple directories

```bash
for ext in py perl js php sh; do mkdir -p "../utils/.llms/cli/$ext"; done
```

Working directory: `/workspace/erp/_inc/laravel`
**Steps**: loop through extensions → create directory tree with parents

### Find and verify directory

```bash
find /workspace/erp/_inc/laravel/tests/Unit -type d -name "frontend" && ls -la /workspace/erp/_inc/laravel/tests/Unit/frontend/
```

**Steps**: find directory → list contents if found

### Find Model classes

```bash
find app/Models -maxdepth 2 -name "*.php" | while read f; do grep -l "^class\|^final class" "$f" | head -1; done | sort | tail -40
```

Working directory: `/workspace/erp/_inc/laravel`
**Steps**: find PHP files (max 2 levels deep) → loop and grep for class declarations → sort → show last 40

### PHP syntax check with filtering

```bash
find tests/Unit/app/Http/Controllers/activity tests/Unit/app/Http/Controllers/auth -name "*Test.php" -exec php -l {} \; 2>&1 | grep -v "^No syntax" | head -20
```

Working directory: `/workspace/erp/_inc/laravel`
**Steps**: find test files → execute php lint on each → merge output → exclude "No syntax" → show first 20
