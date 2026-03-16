# Guia TypeScript / TypeScript Guide

> Para subagentes trabalhando com migração TS em `_inc/laravel/ts/`.

## Estrutura

```
ts/
├── src/                    # TypeScript source (1,167 files)
│   ├── public/             # Mirrors public/ JS
│   ├── resources/          # Mirrors resources/ JS
│   ├── tests/              # Mirrors tests/ JS
│   └── types/globals.d.ts  # Type declarations
├── dist/                   # ESM output (1,127 files)
├── dist-iife/              # IIFE output (1,102 files)
├── scripts/esm-to-iife.cjs # ESM→IIFE converter
├── tsconfig.json           # ES2020 target, strict mode
└── jest.config.cjs         # Jest config for TS
```

## Correspondência de Arquivos

- `public/assets/js/example.js` → `ts/src/public/assets/js/example.ts`
- `resources/js/example.js` → `ts/src/resources/js/example.ts`

## Path Aliases

- `@/*` → `./src/*`
- `@public/*` → `./src/public/*`
- `@resources/*` → `./src/resources/*`
- `@tests/*` → `./src/tests/*`

## Core Singletons

- `erp-bootstrap.ts` — Bootstrap 5 integration
- `erp-guard.ts` — Permission guards (deduplicou 420 padrões)
- `erp-utils.ts` — Shared utilities (deduplicou 601 toast patterns)
- Barrel: `index.ts`

## globals.d.ts

Pre-configured para: Bootstrap 5, jQuery, DataTables, Select2, Summernote, ApexCharts, Flatpickr, SweetAlert2, Toastr, Feather Icons, PerfectScrollbar.

## Comandos (de dentro de `ts/`)

```bash
npm run typecheck      # Type checking
npm run build          # Build ESM
npm run build:watch    # Watch mode
npm run lint           # ESLint
npm run test           # Jest
```

## Status (2026-03-14)

- 1,097/1,097 rotas TS com paridade completa
- 0 erros tsc
- Jest: 1,458/1,458 tests passing
- IIFE build: 1,102 arquivos em `dist-iife/`

## Próximo Passo

Substituir `public/assets/js/routes/` com output de `ts/dist-iife/`. `erp-core.js` deve ser carregado no footer do Blade antes dos scripts de rota.
