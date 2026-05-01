# Text Processing Commands - February 5, 2026

## Actual Commands Executed

### Show last 20 lines of npm test output

```bash
npm test -- --silent 2>&1 | tail -20
```

Working directory: `/workspace/erp/_inc/laravel/tests/frontend/js`

### Show last 30 lines of npm test output

```bash
npm test -- --silent 2>&1 | tail -30
```

Working directory: `/workspace/erp/_inc/laravel/tests/frontend/js`

### Show first 50 lines of npm test output

```bash
npm test -- --silent 2>&1 | head -50
```

Working directory: `/workspace/erp/_inc/laravel/tests/frontend/js`

### Show first 20 lines of directory listing

```bash
ls -la /workspace/erp/_inc/laravel/public/assets/js/pages/ | head -20
```

### Filter test output for status lines

```bash
npm test -- --verbose 2>&1 | grep -E "PASS|FAIL|Tests:" | head -10
```

Working directory: `/workspace/erp/_inc/laravel/tests/frontend/js`

### Extract file sizes and paths

```bash
find /workspace/erp/_inc/laravel/public/assets/js -name "*.js" -type f -size +5k -not -path "*/node_modules/*" | grep -v ".min.js" | xargs ls -lh | awk '{print $5, $9}' | head -20
```

### Exclude minified files

```bash
find /workspace/erp/_inc/laravel/public/assets/js -name "*.js" -type f -not -path "*/node_modules/*" | grep -E "(core|generic|pages)" | grep -v ".min.js" | head -20
```

### Count files

```bash
find /workspace/erp/_inc/laravel/public/assets/js -name "*.js" -type f | wc -l
```

Result: 1306

### Count test files

```bash
find /workspace/erp/_inc/laravel/tests/Unit/frontend/js -type f | wc -l
```

Result: 8

### Suppress errors in ls

```bash
ls -la /workspace/erp/_inc/laravel/public/assets/js/*.js 2>/dev/null
```

### Find Model classes with grep

```bash
find app/Models -maxdepth 2 -name "*.php" | while read f; do grep -l "^class\|^final class" "$f" | head -1; done | sort | tail -40
```

Working directory: `/workspace/erp/_inc/laravel`
