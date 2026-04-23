# ESM / IIFE Module Strategy

> Last updated: 2026-03-11

## Overview

During the TypeScript migration period, all route files use **ESM (`type="module"`)** in both source and compiled output. At final JS replacement time, each file must be converted to an **IIFE (Immediately Invoked Function Expression)** wrapper to match the original `<script defer>` loading pattern used by the Blade views.

## Current State

| Phase                | Module Format             | Script Tag               | Status     |
| -------------------- | ------------------------- | ------------------------ | ---------- |
| **TS Development**   | ESM (`export {}`)         | `<script type="module">` | ✅ Active  |
| **Test Harness**     | ESM                       | `<script type="module">` | ✅ Active  |
| **Final Production** | IIFE (no imports/exports) | `<script defer>`         | ❌ Not yet |

## Why ESM Now?

1. TypeScript's `module: "ESNext"` emits ESM output naturally.
2. `export {}` at the end of each file prevents TS2669 (augmentation in non-module).
3. Allows future refactoring to actually import from `core/` modules.
4. Harness HTML pages load scripts with `type="module"` for test isolation.

## Why IIFE at Final Compile?

1. The Blade footer loads route scripts via `<script defer>` — not `type="module"`.
2. Original JS files are all IIFE-wrapped — the compiled output must match.
3. `type="module"` has different scoping and execution timing than `defer`.
4. Legacy jQuery plugins and global vars (`$`, `show_toastr`, etc.) expect non-module context.

## Conversion Rules

When converting ESM → IIFE for production:

1. **Remove** `export {};` at the end of each file.
2. **Remove** any `import` statements (core utilities become globals or are inlined).
3. **Wrap** the file body in `(function() { ... })();` if not already wrapped.
4. **Strip** `declare global { ... }` blocks (only needed for TS compiler).
5. **Keep** all runtime logic unchanged.

## Automation

Run the ESM → IIFE conversion script:

```bash
node _inc/laravel/ts/scripts/esm-to-iife.cjs
```

This script:

- Reads all `.js` files from `ts/dist/public/assets/js/routes/`
- Strips `export {};` and `import` statements
- Wraps in IIFE if not already wrapped
- Outputs to `ts/dist-iife/public/assets/js/routes/`
- Logs a summary of files processed

## Reverting

To go back to ESM (for development/testing):

- Simply re-run `npx tsc` — the dist/ output is always ESM.
- The `dist-iife/` folder is a separate output that doesn't affect `dist/`.

## Important Notes

- **Never deploy `dist/` directly** — always use `dist-iife/` for production.
- **Tests always run against ESM** output in `dist/`.
- The core singletons (`erp-bootstrap`, `erp-guard`, `erp-utils`) will be bundled into a single `erp-core.js` file for production, loaded before route scripts.
