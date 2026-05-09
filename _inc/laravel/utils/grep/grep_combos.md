# ════════════════════════════════════════════════════════════════

# grep_combos.md — Useful grep flag combinations for ERP codebase

# ════════════════════════════════════════════════════════════════

#

# Quick reference for common grep patterns when auditing the

# erp_brand_new_ideas_company Laravel codebase. Each section has the command,

# what it finds, and when to use it.

## Table of Contents

1. [Basic Flags Reference](#basic-flags-reference)
2. [PHP / Laravel Patterns](#php--laravel-patterns)
3. [JavaScript Patterns](#javascript-patterns)
4. [Security Auditing](#security-auditing)
5. [Database & Migration Patterns](#database--migration-patterns)
6. [Git + Grep Combos](#git--grep-combos)
7. [Performance / Debug Patterns](#performance--debug-patterns)

---

## Basic Flags Reference

| Flag            | Meaning                    | Example                                  |
| --------------- | -------------------------- | ---------------------------------------- | ----- | -------- |
| `-r`            | Recursive                  | `grep -r "TODO" .`                       |
| `-n`            | Line numbers               | `grep -rn "TODO" .`                      |
| `-l`            | Files only (no content)    | `grep -rl "TODO" .`                      |
| `-L`            | Files NOT matching         | `grep -rL "use strict" resources/js/`    |
| `-c`            | Count matches per file     | `grep -rc "TODO" app/`                   |
| `-i`            | Case-insensitive           | `grep -ri "password" .`                  |
| `-w`            | Whole word only            | `grep -rw "user" app/`                   |
| `-P`            | Perl-compatible regex      | `grep -rP "\bdd\(" app/`                 |
| `-E`            | Extended regex             | `grep -rE "TODO                          | FIXME | HACK" .` |
| `-o`            | Only matching part         | `grep -roP "Route::\w+" routes/`         |
| `-A N`          | N lines after match        | `grep -rn -A3 "catch" app/`              |
| `-B N`          | N lines before match       | `grep -rn -B2 "throw" app/`              |
| `-C N`          | N lines context            | `grep -rn -C3 "->find(" app/`            |
| `--include`     | Filter by glob             | `grep -rn --include="*.php" "dd(" .`     |
| `--exclude-dir` | Skip directories           | `grep -rn --exclude-dir=vendor "TODO" .` |
| `-v`            | Invert match               | `grep -rv "^$" file.txt`                 |
| `-h`            | Hide filenames             | `grep -rh "class.*Controller" app/`      |
| `-Z`            | Null-delimited (for xargs) | `grep -rlZ "TODO" . \| xargs -0 wc -l`   |

---

## PHP / Laravel Patterns

### Find all dd() / dump() / var_dump() (debug leftovers)

```bash
grep -rnP '\b(dd|dump|var_dump|print_r|ray)\s*\(' \
  --include="*.php" --exclude-dir=vendor --exclude-dir=tests .
```

### Find raw SQL queries (SQL injection risk)

```bash
grep -rnP 'DB::raw\(|->whereRaw\(|->selectRaw\(|->havingRaw\(' \
  --include="*.php" --exclude-dir=vendor .
```

### Find hardcoded credentials / secrets

```bash
grep -rniE "(password|secret|api_key|token)\s*[=:]\s*['\"][^'\"]{3,}" \
  --include="*.php" --include="*.env*" --exclude-dir=vendor .
```

### Find unused use statements (imports)

```bash
# Lists all `use` imports, then checks if the class name appears elsewhere in the file
grep -rnP '^use\s+\S+\\(\w+);' --include="*.php" --exclude-dir=vendor app/ | while IFS=: read -r f l content; do
  class=$(echo "$content" | grep -oP '\w+(?=;$)')
  count=$(grep -c "\b${class}\b" "$f" 2>/dev/null || echo 0)
  if [[ "$count" -le 1 ]]; then
    echo "UNUSED: $f:$l $content"
  fi
done
```

### Find models without SoftDeletes

```bash
grep -rL "SoftDeletes" --include="*.php" app/Models/ | \
  xargs -I{} grep -l "class.*extends.*Model" {}
```

### Find controllers without middleware

```bash
grep -rL "middleware" --include="*.php" app/Http/Controllers/ | \
  xargs -I{} grep -l "class.*Controller" {}
```

### Find N+1 query risks (accessing relationship in loops)

```bash
grep -rnP '->\w+\s*->\s*(where|find|first|get|count|sum)\(' \
  --include="*.php" --exclude-dir=vendor app/
```

### Find deprecated Laravel helpers

```bash
grep -rnP '\b(str_slug|array_get|array_set|array_first|array_last|str_contains|str_limit)\s*\(' \
  --include="*.php" --exclude-dir=vendor .
```

### Find env() calls outside config files (anti-pattern)

```bash
grep -rnP '\benv\s*\(' --include="*.php" --exclude-dir=vendor --exclude-dir=config .
```

### Find mass assignment risks (no $fillable / $guarded)

```bash
grep -rL '\$(fillable|guarded)' --include="*.php" app/Models/ | \
  xargs -I{} grep -l "extends Model" {}
```

---

## JavaScript Patterns

### Find console.log / console.error (debug leftovers)

```bash
grep -rnP 'console\.(log|error|warn|debug|info|table)\(' \
  --include="*.js" --exclude-dir=node_modules --exclude-dir=vendor \
  --exclude-dir=plugins public/assets/js/routes/
```

### Find jQuery instead of vanilla JS

```bash
grep -rnP '\$\(\s*["\x27]' \
  --include="*.js" --exclude-dir=plugins public/assets/js/routes/
```

### Find inline event handlers in Blade/HTML

```bash
grep -rnP '\b(onclick|onsubmit|onchange|onload|onerror)\s*=' \
  --include="*.blade.php" --include="*.html" resources/views/
```

### Find fetch/XHR without error handling

```bash
grep -rnP 'fetch\(' --include="*.js" --exclude-dir=node_modules public/assets/js/ | \
  grep -v "catch\|\.then.*\.catch"
```

### Find var declarations (should be let/const)

```bash
grep -rnP '^\s*var\s+' --include="*.js" --exclude-dir=node_modules \
  --exclude-dir=plugins --exclude-dir=vendor public/assets/js/routes/
```

---

## Security Auditing

### Find unescaped output in Blade (XSS risk)

```bash
grep -rnP '\{!!\s*\$' --include="*.blade.php" resources/views/
```

### Find eval / exec / system calls

```bash
grep -rnP '\b(eval|exec|system|passthru|shell_exec|proc_open|popen)\s*\(' \
  --include="*.php" --exclude-dir=vendor .
```

### Find files with 777 permissions

```bash
find . -type f -perm 0777 -not -path "./vendor/*" -not -path "./node_modules/*"
```

### Find .env files committed

```bash
find . -name ".env*" -not -name ".env.example" -not -path "./vendor/*"
```

### Find CORS wildcards

```bash
grep -rnP "allowed_(origins|methods|headers).*\*" \
  --include="*.php" config/
```

### Find missing CSRF protection

```bash
grep -rnP 'Route::(post|put|patch|delete)' --include="*.php" routes/ | \
  grep -v "middleware.*csrf\|VerifyCsrfToken\|@csrf"
```

---

## Database & Migration Patterns

### Find migrations with down() that does nothing

```bash
grep -rnP 'function\s+down\s*\(\)' --include="*.php" -A5 database/migrations/ | \
  grep -P "(//|Schema::)"
```

### Find nullable columns

```bash
grep -rnP '->nullable\(\)' --include="*.php" database/migrations/
```

### Find foreign keys without onDelete

```bash
grep -rnP '->foreign\(' --include="*.php" database/migrations/ | \
  grep -v "onDelete"
```

### Find seeder classes

```bash
grep -roP '\w+Seeder::class' --include="*.php" database/seeders/ | sort -u
```

### Count models vs migration tables

```bash
echo "Models:"; find app/Models -name "*.php" | wc -l
echo "Migrations:"; find database/migrations -name "*.php" | wc -l
```

---

## Git + Grep Combos

### Search only staged files

```bash
git diff --cached --name-only | xargs grep -nP "dd\(|var_dump\("
```

### Search only staged files for code flags

```bash
git diff --cached --name-only --diff-filter=ACM | \
  xargs -r grep -nEi 'todo|to[-_ ]?do|fix[-_ ]?me|hack|xxx|deferred|follow[-_ ]?up|temporary|work[-_ ]?around|mock|fake|stub'
```

### Search changed files for production-hardening reminders

```bash
git diff --name-only --diff-filter=ACM | \
  xargs -r grep -nEi 'prod(uction)?|go[-_ ]?live|release|hardening|remove before|replace before'
```

### Search for temporary test seams before handoff

```bash
grep -rniE 'aliasMock|overload:|resetTestSeams|Mock[A-Za-z0-9_]*Gateway|setGateway\s*\(|markTest(Skipped|Incomplete)\s*\(' \
  tests app Modules --include="*.php" --exclude-dir=vendor
```

### Verify legacy brand strings are absent from tracked files

```bash
git grep -niE 'legacy[ -_]?brand|old[ -_]?vendor|white[ -_]?label'
```

### Search only changed files (vs main)

```bash
git diff main --name-only -- '*.php' | xargs grep -nP "TODO|FIXME"
```

### Find who last touched lines containing a pattern

```bash
git grep -n "dd(" -- "*.php" | while IFS=: read -r f l _; do
  echo "$(git blame -L"$l,$l" "$f" | awk '{print $1, $2, $3}'): $f:$l"
done
```

### Search commit messages

```bash
git log --all --oneline --grep="fix\|bug\|hotfix" | head -20
```

### Find large files in history

```bash
git rev-list --objects --all | \
  git cat-file --batch-check='%(objecttype) %(objectname) %(objectsize) %(rest)' | \
  awk '/^blob/{print $3, $4}' | sort -rn | head -20
```

---

## Performance / Debug Patterns

### Find sleep() calls (performance anti-pattern)

```bash
grep -rnP '\bsleep\s*\(' --include="*.php" --exclude-dir=vendor .
```

### Find N+1 eager-loading candidates

```bash
grep -rnP '->with\(' --include="*.php" app/ | \
  grep -c "" && echo "--- vs non-eager ---" && \
  grep -rnP '->\w+\(\)->get\(\)' --include="*.php" app/ | grep -c ""
```

### Find TODO/FIXME/HACK/XXX annotations

```bash
grep -rnE "(TODO|FIXME|HACK|XXX|OPTIMIZE|REVIEW):" \
  --include="*.php" --include="*.js" --include="*.blade.php" \
  --exclude-dir=vendor --exclude-dir=node_modules .
```

### Count lines per file type

```bash
find . -type f \( -name "*.php" -o -name "*.js" -o -name "*.blade.php" \) \
  -not -path "*/vendor/*" -not -path "*/node_modules/*" | \
  xargs wc -l | sort -rn | head -20
```

### Find files over 500 lines (complexity smell)

```bash
find . -type f -name "*.php" -not -path "*/vendor/*" | \
  xargs wc -l | awk '$1 > 500 {print}' | sort -rn
```
