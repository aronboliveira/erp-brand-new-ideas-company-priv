# Greps — ERP Brand New Ideas Company Debugging

## PHP / Laravel

`grep -rn 'dd(\|dump(\|var_dump(' app/ --include='*.php'` — Debug output left in code

`grep -rn 'env(' app/ config/ --include='*.php' | grep -v 'config/'` — Direct env() outside config

`grep -rn 'DB::raw(' app/ --include='*.php'` — Raw SQL usage

`grep -rn 'redirect()->back()' app/ --include='*.php'` — Redirect-back (loop risk)

`grep -rn '?int \$.*[Ii]d' app/ --include='*.php'` — Nullable int ID params (UUID mismatch)

`grep -rn 'Auth::user()->id' app/ --include='*.php'` — Inline auth ID access (null-unsafe)

`grep -rn 'catch (\Throwable' app/ --include='*.php'` — Broad exception catches

`grep -rn 'class.*extends Model' app/Models/ --include='*.php'` — All Eloquent models

`grep -rn 'public function.*(): ' app/Http/Controllers/ --include='*.php'` — Controller method signatures

`grep -rn "route('" resources/views/ --include='*.blade.php'` — Blade route() calls

`grep -rn '__(' resources/views/ --include='*.blade.php' | grep -oP "__\('[^']+'\)" | sort -u` — Translation keys used in views

`grep -rn 'middleware' routes/web.php` — Middleware assignments in routes

`grep -rn 'Log::error\|Log::critical' app/ --include='*.php'` — Error-level log calls

`grep -rn 'TODO\|FIXME\|HACK\|XXX' app/ --include='*.php'` — Code annotations

`grep -rn 'protected \$fillable' app/Models/ --include='*.php'` — Mass-assignment fillable

`grep -rn 'protected \$guarded' app/Models/ --include='*.php'` — Mass-assignment guarded

`grep -rn 'protected \$casts' app/Models/ --include='*.php'` — Attribute casts

`grep -rn 'use HasFactory\|use SoftDeletes\|use HasUuids' app/Models/ --include='*.php'` — Model traits

`grep -rn 'Schema::create\|Schema::table' database/migrations/ --include='*.php'` — Migration operations

`grep -rn "extends Controller" app/Http/Controllers/ --include='*.php'` — Controller inheritance

`grep -rn 'return view(' app/Http/Controllers/ --include='*.php'` — View returns from controllers

`grep -rn 'hasOne\|hasMany\|belongsTo\|belongsToMany\|morphTo\|morphMany' app/Models/ --include='*.php'` — Relationship definitions

`grep -rn 'public const\|protected const' app/ --include='*.php' | head -50` — Constant declarations

`grep -rn 'implements\|extends' app/ --include='*.php' | grep -v vendor` — Inheritance and interfaces

`grep -rn '$request->validate\|$this->validate' app/Http/ --include='*.php'` — Inline validation

`grep -rl 'class.*Controller' app/Http/Controllers/ --include='*.php' | wc -l` — Count controllers

`grep -rl 'class.*extends Model' app/Models/ --include='*.php' | wc -l` — Count models

## JavaScript / TypeScript

`grep -rn 'alert(\|confirm(\|prompt(' public/assets/js/ --include='*.js'` — Native dialogs

`grep -rn 'console.log\|console.error\|console.warn' public/assets/js/ --include='*.js'` — Console output

`grep -rn 'eval(\|new Function(' public/assets/js/ --include='*.js'` — Dynamic code execution

`grep -rn 'innerHTML\s*=' public/assets/js/ --include='*.js'` — innerHTML (XSS risk)

`grep -rn 'fetch(\|axios\|XMLHttpRequest' public/assets/js/ --include='*.js'` — HTTP calls

`grep -rn 'addEventListener' public/assets/js/ --include='*.js'` — Event listeners

`grep -rn 'localStorage\|sessionStorage' public/assets/js/ --include='*.js'` — Storage access

## Blade Templates

`grep -rn '@extends\|@section\|@yield\|@include' resources/views/ --include='*.blade.php' | head -40` — Layout structure

`grep -rn '@can\|@cannot\|@auth\|@guest' resources/views/ --include='*.blade.php'` — Authorization directives

`grep -rn '@csrf' resources/views/ --include='*.blade.php' | wc -l` — CSRF token presence

`grep -rn '<form' resources/views/ --include='*.blade.php' | wc -l` — Form count

`grep -rn '{!!' resources/views/ --include='*.blade.php'` — Unescaped Blade output (XSS risk)

## Configuration & Routes

`grep -rn "Route::resource\|Route::apiResource\|R::resource" routes/ --include='*.php'` — Resource routes

`grep -rn "->middleware(" routes/ --include='*.php'` — Middleware on routes

`grep -rn "'driver'\s*=>" config/ --include='*.php'` — Driver configuration

## Translations

`grep -c '"' resources/lang/en.json` — Count English translation entries

`diff <(jq -r 'keys[]' resources/lang/en.json | sort) <(jq -r 'keys[]' resources/lang/pt-br.json | sort)` — Missing keys between en and pt-br

## Cross-cutting

`grep -rn 'DEPRECATED\|deprecated' app/ resources/ routes/ --include='*.php' --include='*.blade.php' --include='*.js'` — Deprecation markers

`grep -rn 'password\|secret\|token' .env` — Sensitive values in env

`grep -rn 'sleep(' app/ --include='*.php'` — Sleep calls (performance)
