# TypeScript Source Migration

This directory contains TypeScript source files that mirror the JavaScript files found in `_inc/laravel/{public,resources,tests}/`.

## Purpose

The goal is to prepare TypeScript files in an isolated environment that would eventually replace the existing JavaScript files. The original JS files remain unchanged until the migration is complete and validated.

## Directory Structure

```
ts/
├── package.json       # Isolated dependencies and scripts
├── tsconfig.json      # TypeScript configuration
├── eslint.config.mjs  # ESLint rules for TypeScript
├── src/               # Mirrors _inc/laravel/ structure
│   ├── public/        # Mirrors public/ (assets/js/, js/, Modules/)
│   ├── resources/     # Mirrors resources/ (js/)
│   └── tests/         # Mirrors tests/ (e2e/, frontend/, postman/)
└── dist/              # Compiled output (gitignored)
```

## Scripts

```bash
# Install dependencies
npm install

# Compile TypeScript to dist/
npm run build

# Watch mode for development
npm run build:watch

# Type-check without emitting
npm run typecheck

# Lint TypeScript files
npm run lint

# Lint and auto-fix
npm run lint:fix
```

## Path Mapping

The TypeScript files mirror the original JS file paths:

| Original JS Location                         | TypeScript Location                                 |
| -------------------------------------------- | --------------------------------------------------- |
| `public/assets/js/dash.js`                   | `ts/src/public/assets/js/dash.ts`                   |
| `public/assets/js/routes/invoices/create.js` | `ts/src/public/assets/js/routes/invoices/create.ts` |
| `resources/js/app.js`                        | `ts/src/resources/js/app.ts`                        |
| `tests/e2e/auth.setup.cjs`                   | `ts/src/tests/e2e/auth.setup.ts`                    |

## Output

Build output goes to `ts/dist/` with the same structure, preserving the mirror. Eventually, these compiled files will replace the original JS files in their respective locations.

## Global Type Declarations

Third-party libraries loaded via CDN are declared in `src/types/globals.d.ts`:

- `feather` (feather-icons)
- `bootstrap` (Bootstrap 5)
- `ApexCharts`
- `flatpickr`
- `PerfectScrollbar`
- etc.

## Migration Status

Track migration progress in `_inc/laravel/.notes/.llms/ts-migration-status.md`.
