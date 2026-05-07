# `ts/` ESLint cleanup — 2026-05-07

## Before
```bash
cd _inc/laravel/ts
npx eslint .
# → 1,175 problems (1,112 errors, 63 warnings)
```

## Breakdown of 1,112 errors
| Source | Count | Nature |
|---|---:|---|
| `dist-iife/public/**` | 1,106 | Build artifact (post-process IIFE output) — should never be linted |
| `.tmp/copilot/scripts/*.cjs` | 2 | Past agents' session scratch — should not be linted |
| `scripts/esm-to-iife.cjs`, `scripts/generate-lang-harness.cjs` | 2 | Parsing error: `.cjs` files not in `tsconfig.json` `parserOptions.project` |
| `src/public/assets/js/routes/customers/url.ts:87` | 1 | `@ts-ignore` → ESLint says use `@ts-expect-error`; **further investigation showed the directive was masking nothing — removed entirely** |
| `src/resources/js/app.ts:9` | 1 | `@ts-ignore` for the type-less `alpinejs` import → converted to `@ts-expect-error` (still valid, tsc confirms it suppresses a real error) |

## Fixes
1. `eslint.config.mjs`: added `dist-iife/**`, `.tmp/**`, `scripts/**/*.cjs` to the global `ignores` array. (`dist/**` was already there but `dist-iife/**` was missed.)
2. `src/public/assets/js/routes/customers/url.ts:87`: removed the dead `@ts-ignore` directive (TS no longer needs it; converting to `@ts-expect-error` triggered TS2578 "Unused directive", proving the suppression was stale).
3. `src/resources/js/app.ts:9`: `@ts-ignore` → `@ts-expect-error` (suppression is still real — alpinejs has no type declarations).

## After
```bash
npx eslint .
# → 63 problems (0 errors, 63 warnings)

npx tsc --noEmit
# → clean
```

The 63 remaining warnings are by design — the config explicitly demotes strict-type rules to `warn` during the migration phase (see header comment in `eslint.config.mjs`: *"WARNINGS: Should be fixed - strict type rules for gradual migration. As files are properly typed, these can be promoted to error."*). Categories: `prefer-nullish-coalescing`, `no-unsafe-*`, `no-explicit-any`, `explicit-function-return-type`, etc.

## Note on the `where-to-update-and-read.yml` directive
The yml says *"##DO NOT WORK ON THE PATHS WITH */ts/* FOR NOW##"*. The user
explicitly authorized this turn's `ts/` ESLint cleanup, so the directive is
treated as lifted for the scope-fix + 2 micro source edits above. No
broad-spectrum source rewrites were done — the 63 warnings remain untouched.
