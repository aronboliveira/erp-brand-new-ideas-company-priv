# Regexes — ERP Prestech Debugging

## PHP / Laravel

`public\s+(?:static\s+)?function\s+\w+` — Public method declarations

`\$table->(?:big)?Increments\('id'\)` — Integer auto-increment PKs (should be UUID)

`class\s+\w+\s+extends\s+Model[\s\S]*?ChecksLogin` — Models mixing in auth traits unsafely

`\{\{\s*route\s*\(\s*['"][a-zA-Z]+\.` — Blade route() calls

`redirect\(\)->back\(\)` — Redirect-back calls (potential loops)

`catch\s*\(\s*\\?Throwable` — Broad Throwable catches

`\?\s*int\s+\$.*[Ii]d` — Nullable int ID params (should be int|string|null for UUIDs)

`Auth::user\(\)->id` — Inline auth user ID access (null-unsafe)

`dd\(|dump\(|var_dump\(|print_r\(` — Debug output left in code

`env\(\s*'` — Direct env() calls outside config files

`DB::raw\(` — Raw SQL (potential injection)

`\$request->input\(` — Unvalidated request input usage

`->where\(\s*'id'` — Hardcoded 'id' column queries

`Log::(error|critical|emergency)\(` — Error/critical log statements

`'middleware'\s*=>\s*\[.*'auth'.*'verified'` — Auth before verified middleware order

`Route::(?:get|post|put|patch|delete)\(.*function\s*\(` — Inline route closures (not cacheable)

`\$this->belongsTo\(.*::class\s*,\s*'.*_id'\)` — BelongsTo with explicit FK (check UUID compat)

`return\s+view\(\s*'[^']+'\s*\)` — View returns without data

`@extends\s*\(\s*'[^']+'\s*\)` — Blade layout inheritance chain

`__\(\s*'[^']+'\s*\)` — Translation function calls

`class\s+\w+Controller\s` — Controller class declarations

`Schema::(?:create|table)\(` — Migration schema operations

`->hasOne\(|->hasMany\(|->belongsTo\(|->belongsToMany\(` — Eloquent relationship definitions

`use\s+App\\Models\\` — Model import statements

## JavaScript / TypeScript

`alert\(|confirm\(|prompt\(` — Native browser dialogs (should use ERPGuard)

`console\.(log|error|warn|debug)\(` — Console output

`document\.querySelector\(` — Direct DOM queries

`eval\(|new Function\(` — Dynamic code execution

`localStorage\.|sessionStorage\.` — Browser storage access

`fetch\(|XMLHttpRequest|axios` — HTTP request calls

`addEventListener\(` — Event listener registrations

`setTimeout\(|setInterval\(` — Timer usage (cleanup needed?)

`\.innerHTML\s*=` — innerHTML assignment (XSS risk)

`\$\.\w+\(|jQuery` — jQuery usage (legacy)

## CSS

`!important` — Specificity overrides

`class="[^"]*\bcontainer\b[^"]*"` — Bootstrap container usage

`z-index:\s*\d{4,}` — Excessively high z-index values

## SQL / Migrations

`->string\(\s*'.*id'\s*\)` — String ID columns (UUID check)

`->unsignedBigInteger\(\s*'.*_id'` — Integer FK columns (UUID mismatch?)

`ON DELETE CASCADE` — Cascade deletes (intentional?)

`->nullable\(\)` — Nullable columns

## Config / Env

`MAIL_DRIVER=log|QUEUE_DRIVER=sync|CACHE_DRIVER=file` — Non-production driver settings

`APP_DEBUG=true` — Debug mode enabled

`DB_PASSWORD=test|DB_PASSWORD=root|DB_PASSWORD=password` — Weak DB passwords
