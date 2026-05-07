# Toolchain run with live server — 2026-05-07

CWD: `_inc/laravel/` unless noted.
Server: `php artisan serve --host=127.0.0.1 --port=8000` (background).
DB state at run: SA user `suporte@brandnewideascompany.com` present (UUID
`a3e8f4b2-...`); 5 users, 4 employees, 8 roles, 1,120 permissions, 24 plans.
`customers/invoices/bills` empty — irrelevant to SQLi probing.

## Pytest with server up

```bash
python3 -m pytest tests/python/security/test_sqli_payloads.py -v
# → 171 passed (was 171 skipped server-down)

python3 -m pytest tests/python/
# → 439 passed (up from 268 passed / 171 skipped)
```

The 7 SQLi test functions × parametrized payload sets all green: no SQLSTATE,
QueryException, PDOException, syntax-error, or `Base table or view not found`
patterns leaked in any HTTP response. WAF / Eloquent / parameter binding
neutralized every payload.

## TSC submodule sweep

| Submodule | tsc | Action |
|---|---|---|
| `_inc/laravel/` (main) | clean (after excluding `frontend/**` + `Modules/LandingPage/**`) | **fixed** |
| `_inc/laravel/ts/` | clean | — |
| `_inc/laravel/tests/frontend/js/` | clean (after `module: commonjs → esnext`) | **fixed** |
| `_inc/laravel/frontend/app/` (Next.js) | ~14 errors (path aliases, missing modules, implicit any) | **out of scope** — separate sub-app; CI marks `tsc --noEmit` continue-on-error |
| `_inc/laravel/Modules/LandingPage/` | no tsconfig | n/a |
| `_inc/laravel/tests/postman/` | no tsconfig | n/a |

### Fixes
1. `tsconfig.json` (main): added `"frontend/**"` and `"Modules/LandingPage/**"`
   to `exclude`. The Next.js sub-app has its own `tsconfig.json` (with `@/*`
   path alias scoped to its own `src/`), and was being pulled into the main
   compile because of the `"include": ["**/*.ts", "**/*.tsx", ...]` glob.
2. `tests/frontend/js/tsconfig.json`: `"module": "commonjs"` was incompatible
   with `"moduleResolution": "bundler"` under TS 5.x (only `preserve`/`es2015+`
   allowed). Switched `module` to `"esnext"`. The Playwright e2e specs
   (`hardening.spec.ts`, `mock-routes-e2e.spec.ts`) use `import.meta` which
   requires this anyway.

## ESLint submodule sweep

| Submodule | eslint | Action |
|---|---|---|
| `_inc/laravel/` (main) | 0 errors / 34 legitimate warnings | — |
| `_inc/laravel/ts/` | **1,112 errors / 63 warnings** | **report only** — `where-to-update-and-read.yml` directive: "DO NOT WORK ON THE PATHS WITH */ts/* FOR NOW" |
| `_inc/laravel/frontend/app/` | `next lint` deprecated in Next 16; runs but warns about workspace root inference (multiple `package-lock.json` files); the script that runs it is in ESM mode but uses `require` | **report only** — separate sub-app baseline |
| `_inc/laravel/Modules/LandingPage/` | no eslint config | n/a |
| `_inc/laravel/tests/frontend/js/` | no own eslint config (covered by main flat config via Jest workspace) | n/a |
| `_inc/laravel/tests/postman/` | no eslint config | n/a |

## Open follow-ups (not in scope of this turn)

- `_inc/laravel/ts/` ESLint: 1,112 errors. Per the do-not-work directive, the
  TS migration tree is frozen. When the directive lifts, this is a meaningful
  cleanup pass — likely the same shape as the main eslint.config.mjs scope
  fix (sub-app artifacts being scanned).
- `_inc/laravel/frontend/app/` tsc: ~14 errors covering missing modules
  (`@material-ui/core`, `bootstrap/dist/js/bootstrap.bundle.min.js`,
  `react-toastify`, `wowjs`), broken path aliases (`@/components/SideNav`,
  `@/components/HRSideNav`), implicit `any` on event handlers, and a
  `LoginPageSettings.recaptcha_module` snake-case mismatch. Either install
  missing deps + add `@types/wowjs` etc., or set the sub-app's tsconfig
  `noImplicitAny: false` until the Next.js port stabilizes.
- `_inc/laravel/frontend/app/` lint: migrate from `next lint` to direct
  ESLint invocation (Next 16 removed `next lint`).
