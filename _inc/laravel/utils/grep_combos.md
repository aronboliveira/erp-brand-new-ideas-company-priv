# Laravel Grep Combos — ERP Prestech

> Quick reference for `grep` one-liners targeting patterns common in this Laravel 10 + Blade + jQuery ERP codebase.
> All paths are relative to `_inc/laravel/`.

---

## 1. Security Auditing

```bash
# Unescaped Blade output (XSS risk)
grep -rPn '\{!!\s*\$' resources/views/ --include='*.blade.php'

# Raw SQL queries — check for parameterization
grep -rPn 'DB::(raw|select|insert|update|delete|statement)\s*\(' app/

# env() outside config/ (anti-pattern, breaks config:cache)
grep -rPn '\benv\s*\(' app/ --include='*.php'

# Mass assignment with $request->all()
grep -rPn '::(create|update)\(\s*\$request->all\(\)' app/

# Hardcoded credentials or tokens
grep -rPnio '(password|secret|token|api_key)\s*=\s*["\047][^"\047]{3,}' app/ config/ .env* 2>/dev/null

# Debug mode in config
grep -rPn "APP_DEBUG.*true|'debug'\s*=>\s*true" config/app.php .env 2>/dev/null

# Unprotected routes (no middleware)
grep -Pn 'Route::(get|post|put|patch|delete)\(' routes/*.php | grep -v 'middleware'
```

## 2. Eloquent & Database

```bash
# N+1 risk: find relationships loaded in Blade loops
grep -rPn '->(\w+)->\w+' resources/views/ --include='*.blade.php' | grep -i 'foreach'

# find() without null guard
grep -rPn '->find\(\$[^)]+\)[^?;]*->' app/ --include='*.php'

# Where clauses with raw strings (SQL injection vector)
grep -rPn "->whereRaw\(|->havingRaw\(|->selectRaw\(" app/ --include='*.php'

# Seeders: list all called seeders in DatabaseSeeder
grep -oP '\$this->call\(\[?\K[^]]+' database/seeders/DatabaseSeeder.php | tr ',' '\n' | sed 's/[[:space:]]//g;s/::class//g' | sort

# Migration files that drop tables
grep -rPn 'Schema::drop\b' database/ --include='*.php'

# Models missing $fillable or $guarded
for f in app/Models/*.php; do grep -qP '\$(fillable|guarded)' "$f" || echo "WARNING: $f missing \$fillable/\$guarded"; done

# Empty tables (run against mysql CLI)
mysql -u admin_prestech -p'76562f3A*@prestech' erp_prestech -e \
  "SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA='erp_prestech' AND TABLE_ROWS=0 ORDER BY TABLE_NAME;"
```

## 3. Routes

```bash
# List all named routes
grep -rPoh "->name\('[^']+'\)" routes/*.php | sort

# Find duplicate route names
grep -rPoh "->name\('[^']+'\)" routes/*.php | sort | uniq -d

# API routes without throttle
grep -Pn 'Route::' routes/api.php | grep -v 'throttle'

# Route-controller mapping
grep -Pn 'Route::(get|post|put|patch|delete)\(' routes/web.php | head -50

# Closures in routes (should use controllers)
grep -Pn 'Route::\w+\([^,]+,\s*function' routes/*.php
```

## 4. Blade Templates

```bash
# Components usage census
grep -rPoh '<x-[\w.-]+' resources/views/ --include='*.blade.php' | sort | uniq -c | sort -rn

# @include directives without existence check
grep -rPn '@include\b' resources/views/ --include='*.blade.php' | grep -v '@includeIf\|@includeWhen\|@includeFirst'

# Forms without @csrf
grep -rPn '<form' resources/views/ --include='*.blade.php' | grep -v '@csrf'

# JavaScript embedded in Blade
grep -rPcn '<script' resources/views/ --include='*.blade.php' | grep -v ':0$'

# Missing @endsection tags
for f in $(find resources/views -name '*.blade.php'); do
  opens=$(grep -c '@section' "$f" 2>/dev/null)
  closes=$(grep -c '@endsection\|@show\|@stop' "$f" 2>/dev/null)
  [[ "$opens" -gt "$closes" ]] && echo "MISMATCH $f: $opens sections, $closes endings"
done
```

## 5. JavaScript (Route Files)

```bash
# var keyword usage (should be const/let)
grep -rPn '\bvar\s+\w+' public/assets/js/routes/

# Undeclared el/form/link access
grep -rPn '\b(el|form|link)\.(getAttribute|setAttribute|href|action)' public/assets/js/routes/

# Console statements left in code
grep -rPn 'console\.(log|warn|error|debug|info|table)\s*\(' public/assets/js/routes/

# jQuery AJAX calls ( $.ajax / $.post / $.get / fetch )
grep -rPcn '\$\.\(ajax\|post\|get\)\|fetch\s*(' public/assets/js/routes/ | grep -v ':0$'

# Blade vars embedded in JS (potential injection)
grep -rPn '\{\{\s*\$\w' public/assets/js/routes/
```

## 6. Testing & Quality

```bash
# Tests with no assertions
for f in $(find tests -name '*Test.php'); do
  methods=$(grep -cP 'function\s+test' "$f")
  asserts=$(grep -cP 'assert|expect' "$f")
  [[ "$methods" -gt "$asserts" ]] && echo "LOW COVERAGE $f: $methods tests, $asserts asserts"
done

# PHPUnit test files count
find tests -name '*Test.php' | wc -l

# Disabled tests (@group skip, markTestSkipped)
grep -rPn 'markTestSkipped\|markTestIncomplete\|@group\s+skip' tests/

# Factories without definition
grep -rPn 'HasFactory' app/Models/ --include='*.php' -l | while read m; do
  class=$(basename "$m" .php)
  [[ ! -f "database/factories/${class}Factory.php" ]] && echo "MISSING FACTORY: $class"
done
```

## 7. Performance

```bash
# Queries inside loops (code smell)
grep -rPn 'foreach.*{' app/ --include='*.php' -A20 | grep -P '(->get\(\)|->first\(\)|->count\(\)|DB::)'

# Large file uploads (check max sizes)
grep -rPn 'max:\d{4,}' app/Http/Requests/ --include='*.php'

# Missing indexes (look for whereHas without index hints)
grep -rPn '->whereHas\(' app/ --include='*.php'

# Cache usage
grep -rPcn 'Cache::\|cache\(\)' app/ --include='*.php' | grep -v ':0$'

# Jobs dispatching (async landscape)
grep -rPn 'dispatch\(new\s\|::dispatch(' app/ --include='*.php'
```

## 8. Useful Combos

```bash
# All TODO / FIXME / HACK comments
grep -rPn '\b(TODO|FIXME|HACK|XXX|TEMP)\b' app/ resources/ routes/ config/ --include='*.php' --include='*.blade.php' --include='*.js'

# Files over 500 lines (candidates for refactoring)
find app/ resources/ routes/ public/assets/js/routes/ -type f \( -name '*.php' -o -name '*.js' \) -exec awk 'END{if(NR>500)print FILENAME": "NR" lines"}' {} \;

# Recently modified files (last 7 days)
find . -type f \( -name '*.php' -o -name '*.js' -o -name '*.blade.php' \) -mtime -7 -not -path './vendor/*' -not -path './node_modules/*' | sort

# Git: files with most commits (hotspots)
git log --name-only --pretty=format: -- '*.php' '*.js' | sort | uniq -c | sort -rn | head -20
```
