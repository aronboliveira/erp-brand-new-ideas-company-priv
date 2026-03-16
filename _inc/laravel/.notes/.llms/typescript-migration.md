# TypeScript Migration Guide

## Overview

The project has migrated JavaScript files from `public/`, `resources/`, and `tests/` directories to TypeScript in an isolated `ts/src/` folder structure. The original JS files remain in place while the TypeScript source files are maintained separately for gradual adoption.

## Directory Structure

```
_inc/laravel/
├── ts/                          # TypeScript submodule
│   ├── src/                     # TypeScript source files (mirrored structure)
│   │   ├── public/              # Mirrors public/ JS files
│   │   ├── resources/           # Mirrors resources/ JS files
│   │   └── tests/               # Mirrors tests/ JS files
│   ├── dist/                    # Compiled output (gitignored)
│   ├── package.json             # TS-specific dependencies
│   ├── tsconfig.json            # TypeScript config
│   ├── jest.config.cjs          # Jest config for TS tests
│   ├── playwright.config.ts     # Playwright config for TS e2e
│   ├── eslint.config.mjs        # ESLint config for TS
│   └── src/types/globals.d.ts   # Type declarations for globals
└── .backup/frontend/original/js/ # Backup of original JS files
```

## Key Configuration Details

### TypeScript (`ts/tsconfig.json`)

- **Target**: ES2020
- **Module**: ES2020 with bundler resolution
- **Strict Mode**: Enabled (all strict checks)
- **Output**: `ts/dist/`
- **Path Aliases**:
  - `@/*` → `./src/*`
  - `@public/*` → `./src/public/*`
  - `@resources/*` → `./src/resources/*`
  - `@tests/*` → `./src/tests/*`

### Type Declarations (`ts/src/types/globals.d.ts`)

Pre-configured types for project globals:

- **Bootstrap 5**: Modal, Toast, Tab, Collapse, Dropdown, Tooltip, Popover, Alert, Offcanvas, Carousel, Scrollspy
- **jQuery**: Full type support with plugins
- **DataTables**: jQuery DataTables API
- **Select2**: jQuery Select2 plugin
- **Summernote**: WYSIWYG editor
- **ApexCharts**: Chart library
- **Flatpickr**: Date picker
- **SweetAlert2**: Alert dialogs
- **Toastr**: Toast notifications
- **Feather Icons**: Icon library
- **PerfectScrollbar**: Custom scrollbar

### Excluded Files

The following were intentionally excluded from migration:

- Vendor libraries (`summernote/`, `tinymce/`, `plugins/`, `node_modules/`, etc.)
- Minified files (`*.min.js`)
- Generated/coverage files (`playwright-report/`, `coverage/`, `dist/`)
- Third-party plugins in `assets/`

## Working with TypeScript Files

### Development Commands

From the `ts/` directory:

```bash
# Type checking
npm run typecheck

# Build to dist/
npm run build

# Watch mode
npm run build:watch

# Linting
npm run lint
npm run lint:fix

# Testing
npm run test
npm run test:watch
npm run test:coverage
```

### Adding New TypeScript Files

1. Create `.ts` file in appropriate `ts/src/` subdirectory
2. Import types from `globals.d.ts` or add new declarations as needed
3. Use path aliases (`@/`, `@public/`, etc.) for imports
4. Run `npm run typecheck` to validate

### Type Annotations

Converted files include:

- JSDoc type headers with `@fileoverview`
- DOM element type assertions: `document.getElementById('x') as HTMLElement`
- Event handler types: `(e: Event) => void`
- Global reference comments for Bootstrap/jQuery integration

## Migration Statistics

- **Total JS files in project**: ~6,831
- **Vendor/minified excluded**: ~5,880
- **Application JS migrated**: 1,097 files (originally 944, expanded via gap-closure sessions)
- **TypeScript files created**: 1,167 files (including core singletons, tests, type helpers)
- **Directories created**: 249+
- **IIFE build output**: 1,102 files in `ts/dist-iife/`
- **ESM build output**: 1,127 files in `ts/dist/`

> Last verified: 2026-03-14 — `ts/src/` directory exists with 1,167 `.ts` files.

## Notes for LLMs

### When modifying TypeScript files:

1. Always maintain strict mode compliance
2. Use proper type assertions for DOM elements
3. Reference globals from `globals.d.ts`
4. Follow existing JSDoc patterns for documentation

### When adding new features:

1. Create TypeScript version in `ts/src/`
2. Add necessary type declarations to `globals.d.ts`
3. Use path aliases for clean imports
4. Run typecheck before committing

### File correspondence:

- `public/assets/js/example.js` → `ts/src/public/assets/js/example.ts`
- `resources/js/example.js` → `ts/src/resources/js/example.ts`
- `tests/js/example.js` → `ts/src/tests/js/example.ts`

## Backup Location

Original JavaScript files are backed up at:

```
.backup/frontend/original/js/
```

This preserves the exact state before TypeScript migration for reference or rollback.
