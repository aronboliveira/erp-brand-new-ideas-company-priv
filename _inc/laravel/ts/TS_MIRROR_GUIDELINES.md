# TypeScript Mirror — Guidelines

## Overview

The `ts/` directory is a **TypeScript mirror** of the JavaScript source files in `_inc/laravel/`.
It contains ~1,180 `.ts` source files and ~87 test files, all derived from their `.js` counterparts.

> **⚠ Source of Truth:** The original `.js` files remain the runtime source. The TS mirror is
> for type safety, refactoring, and test coverage improvements.

---

## Directory Structure

```
ts/
├── src/                     # Mirrored TS source files
│   ├── public/assets/js/    # Core and route scripts
│   ├── resources/js/        # Bootstrap and framework setup
│   └── tests/               # Mirrored test specs (e2e, unit)
├── tests/                   # Additional test suites
│   ├── Unit/                # Jest unit tests
│   ├── Feature/             # Feature/roleplay scripts
│   ├── e2e/                 # Playwright e2e specs
│   └── harness*/            # Harness HTML pages for Playwright
├── dist/                    # tsc output (gitignored)
├── tsconfig.json            # Main TypeScript config
├── jest.config.cjs          # Jest config (CJS mode)
├── eslint.config.mjs        # ESLint flat config
├── playwright.config.ts     # Playwright e2e config
└── playwright.harness.config.ts  # Playwright harness config
```

---

## Build & Test Commands

### Build (TypeScript Compilation)

```bash
cd _inc/laravel/ts
npx tsc --build
```

### Lint (ESLint)

```bash
npx eslint src --max-warnings=80
```

Current baseline: **0 errors, ~71 warnings** (all migration-related).

### Unit Tests (Jest)

```bash
npx jest --no-coverage              # Full suite
npx jest tests/Unit/frontend/       # Subset
npx jest --no-coverage --verbose    # Verbose output
```

Current: **37 suites, 701 tests, all passing**.

### E2E Tests (Playwright)

```bash
# Requires running Laravel server at http://localhost:8000
npx playwright test --config=playwright.config.ts

# Harness tests (self-contained server on :3333)
npx playwright test --config=playwright.harness.config.ts
```

**Note:** E2E tests require `php artisan serve` running. Harness specs are not yet authored.

### Full Pipeline

```bash
npm run build && npm run lint && npm run test
```

---

## MySQL Test Database

When tests require a MySQL database, follow this pattern:

```bash
# Create
mysql -u test -ptest -e "
CREATE DATABASE IF NOT EXISTS \`erp-prestech-ts-test\`;
USE \`erp-prestech-ts-test\`;
CREATE TABLE IF NOT EXISTS \`erp-prestech-ts-test\` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  test_name VARCHAR(255) NOT NULL,
  status ENUM('pass','fail','skip') NOT NULL DEFAULT 'pass',
  suite VARCHAR(100),
  duration_ms INT UNSIGNED DEFAULT 0,
  run_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
"

# Destroy after tests
mysql -u test -ptest -e "DROP DATABASE IF EXISTS \`erp-prestech-ts-test\`;"
```

Credentials from `.env`: `DB_USERNAME=test`, `DB_PASSWORD=test`, `DB_HOST=127.0.0.1:3306`.

---

## Key Conventions

### Module System

- `package.json` has `"type": "module"` (ESM by default)
- **Jest runs in CJS mode** via `ts-jest` with `useESM: false`
- `jest.config.cjs` maps `.js` imports to extensionless: `"^(\\.{1,2}/.*)\\.js$": "$1"`
- Feature scripts must NOT use `import.meta.url` (SyntaxError in CJS/Jest context)

### Dual CJS/ESM Guard Pattern

For scripts that run both via Jest (`require()`) and standalone (`node --experimental-strip-types`):

```typescript
// ✅ Works in both CJS (Jest) and ESM (node direct execution)
if (process.argv[1]?.endsWith("script_name.ts")) {
  main();
}

// ❌ NEVER use — SyntaxError in CJS mode
if (import.meta.url === `file://${process.argv[1]}`) { ... }

// ❌ NEVER use — undefined in ESM mode
if (require.main === module) { ... }
```

### `__dirname` Replacement

- In Playwright specs: use `import.meta.dirname` (Node 22+)
- In Feature scripts loaded by Jest: use `(globalThis as any).__dirname ?? path.dirname(process.argv[1] || ".")`
- **Never** redeclare `const __dirname = ...` — it conflicts with CJS's injected `__dirname`

### Window Globals in Tests

IIFE-based source files (e.g., `route-guard.ts`) set `window.RouteGuard` when imported.
Do NOT overwrite with ESM exports:

```typescript
// ✅ Correct — import for side effects, IIFE handles window assignment
await import(ROUTE_MODULE);

// ❌ Wrong — overwrites IIFE's window assignment with empty ESM exports
const mod = await import(ROUTE_MODULE);
(window as any).RouteGuard = mod;
```

For utility modules (e.g., `erp-guard.ts`, `erp-utils.ts`), import and test exports directly:

```typescript
const mod = await import(MODULE_PATH);
expect(mod.isGuarded(el)).toBe(true);
```

### Test File Structure

```typescript
/**
 * @file test-name.test.ts
 * @description Testes unitários para ...
 * PULL REQUEST START
 */
export {}; // ensures module scope (prevents variable redeclaration across files)

const MODULE_PATH = "../../../../../src/path/to/module";

describe("Module Name", () => {
  let mod: typeof import("../../../../../src/path/to/module");

  beforeEach(async () => {
    jest.resetModules();
    document.body.innerHTML = "";
    mod = await import(MODULE_PATH);
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  test("function does X", () => {
    expect(mod.functionName(args)).toBe(expected);
  });
});
```

### Audit Test Baselines

Three audit tests track code quality of the original JS codebase:

- **`frontend-performance-budgets`**: Mock HTML fixtures ≤ 250KB, route scripts ≤ 25KB
- **`frontend-risk-audit`**: Known baselines for `eval` (≤2), `document.write` (≤1), `innerHTML` (≤10)
- **`mock-pages.integrity`**: Known ≤ 8 unresolved local references (dynamic Laravel routes)

Update baseline counts when fixing original JS or adding new violations.

### Pull Request Markers

All backend changes must be wrapped:

```typescript
// PULL REQUEST START
// ... code ...
// PULL REQUEST END
```

### Vendor File Custom Changes

When modifying vendor/third-party JS files (e.g., LandingPage templates, Chatify), the PR markers
must include the **reason** and a vendor label so they survive upstream merges and are easy to grep:

```js
// PULL REQUEST START — Alteração customizada em arquivo vendor (null guards)
// ... código ...
// PULL REQUEST END — Fim da alteração customizada
```

Grep all custom vendor patches:

```bash
grep -rn 'Alteração customizada em arquivo vendor' Modules/ public/
```

### Security — XSS Sanitization in Chatify

`public/js/chatify/code.js` (and its TS mirror) contain two helper functions added as custom patches:

- **`_sanitizeHtml(html)`** — Strips `<script>`, `on*` event attributes, `javascript:` protocol URIs,
  and `<iframe>/<object>/<embed>` from server-rendered HTML before DOM insertion via `.html()`.
- **`_safeCssUrl(rawUrl)`** — Validates that CSS `background-image` URLs use only `http(s)` or `/`
  protocols, preventing CSS injection via `url("javascript:...")` or data URIs.

Additionally, `.html(data.fetch.name)` calls were replaced with `.text(data.fetch.name)` for plain-text
user names that never need HTML rendering.

> **⚠ Do NOT remove these patches** when updating the Chatify vendor package.
> Re-apply them after any vendor update. They mitigate stored XSS via AJAX responses.

### Comments & Documentation

Additional comments in backend-related edits should be in **pt-BR**.

---

## Known Limitations

1. **TS sources are NOT runtime replacements** — The TS files are simplified extractions of the original JS IIFE singletons. The original JS remains the runtime code loaded by Blade templates.

2. **E2E tests require Laravel** — Playwright specs need `php artisan serve` running on `:8000`.

3. **Harness specs relocated** — Harness specs were moved from `tests/harness-specs/` into `src/tests/`. The old directory has been removed.

4. **`forceConsistentCasingInFileNames: false`** — Disabled due to `payslip.ts`/`Payslip.ts` casing conflict. `payslip.ts` is excluded from both tsconfig and eslint.

5. **Vendor file patches** — Custom null-guard and XSS fixes have been applied to LandingPage JS files and Chatify `code.js`. These must be re-applied after upstream vendor updates.

---

## Troubleshooting

| Symptom                                            | Fix                                                                                 |
| -------------------------------------------------- | ----------------------------------------------------------------------------------- |
| `Cannot use 'import.meta' outside a module`        | Use `process.argv[1]?.endsWith()` guard instead                                     |
| `Identifier '__dirname' has already been declared` | Rename to `_scriptDir` or use `(globalThis as any).__dirname`                       |
| `Cannot find module './foo.js'`                    | jest.config.cjs `moduleNameMapper` strips `.js` extensions                          |
| `Property 'X' does not exist` in test              | Source exports differ from original JS API — test the TS exports directly           |
| `fetch does not exist` in jsdom                    | Add `if (!globalThis.fetch) (globalThis as any).fetch = jest.fn();` in `beforeEach` |
| E2E auth fails                                     | Start Laravel: `cd _inc/laravel && php artisan serve`                               |
