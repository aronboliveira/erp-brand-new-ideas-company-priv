# File Search Commands - February 5, 2026

## Actual Commands Executed

### Find route-guard.js file

```bash
find /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/public/assets/js -name "route-guard.js" -type f
```

Result: `/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/public/assets/js/core/route-guard.js`

### Find frontend test directory

```bash
find /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/tests/Unit -type d -name "frontend"
```

Result: `/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/tests/Unit/frontend`

### List frontend test directory contents

```bash
ls -la /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/tests/Unit/frontend/
```

### Find PHP and TypeScript files in test directory

```bash
find /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/tests/Unit/frontend/js -name "*.php" -o -name "*.ts" | head -20
```

### Count files in Unit/frontend/js

```bash
find /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/tests/Unit/frontend/js -type f | wc -l
```

Result: 8 files

### Find testable JavaScript files

```bash
find /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/public/assets/js -name "*.js" -type f -not -path "*/node_modules/*" | grep -E "(core|generic|pages)" | grep -v ".min.js" | head -20
```

### Find JavaScript files over 5KB

```bash
find /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/public/assets/js -name "*.js" -type f -size +5k -not -path "*/node_modules/*" | grep -v ".min.js" | xargs ls -lh | awk '{print $5, $9}' | head -20
```

### Count all JavaScript files

```bash
find /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/public/assets/js -name "*.js" -type f | wc -l
```

Result: 1306 files

### Find generic JavaScript files

```bash
find /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/public/assets/js/generic -name "*.js" -type f
```

Result: `/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/public/assets/js/generic/checkMounted.js`

### List core JavaScript files

```bash
ls -lh /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/public/assets/js/core/
```

### List pages JavaScript files

```bash
ls -la /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/public/assets/js/pages/ | head -20
```

### List root JavaScript files

```bash
ls -la /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/public/assets/js/*.js 2>/dev/null
```

### Find Model classes (from laravel directory)

```bash
find app/Models -maxdepth 2 -name "*.php" | while read f; do grep -l "^class\|^final class" "$f" | head -1; done | sort | tail -40
```

Working directory: `/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel`

### Search for ERPGuard class (case-insensitive)

```bash
grep -ril "class erpguard"
```

Working directory: `/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel`
Exit code: 1 (not found)
