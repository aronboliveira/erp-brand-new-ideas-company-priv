# Padrões de JS Singletons / JS Singleton Patterns

> Para subagentes trabalhando com assets JavaScript em `public/assets/js/`.

## Core Singletons

O projeto usa singletons IIFE para compartilhar funcionalidade entre scripts de rota:

```javascript
// erp-bootstrap.ts → erp-core.js (carregado primeiro no footer)
window.ERPBootstrap = (function () {
  // Bootstrap 5 component initialization
  // Toast notifications
  // Permission guards
  return { init, showToast, checkPermission };
})();
```

## IIFE Build Strategy

1. TypeScript ESM em `ts/src/` → compilado para `ts/dist/` (ESM)
2. `ts/scripts/esm-to-iife.cjs` → converte para `ts/dist-iife/` (IIFE)
3. IIFE output pode substituir `public/assets/js/routes/` diretamente

### Por que IIFE?

- Blade templates carregam scripts via `<script src="">` — não suportam ESM `import`
- IIFE wrapping preserva escopo e evita poluição global
- Cada arquivo de rota é auto-contido

## Convenções

### 1. Window Augmentation

```typescript
declare global {
  interface Window {
    ERPBootstrap: typeof ERPBootstrap;
  }
}
```

### 2. DOM Type Assertions

```typescript
const el = document.getElementById("myId") as HTMLInputElement;
```

### 3. Event Handlers

```typescript
el.addEventListener("click", (e: MouseEvent): void => {
  // ...
});
```

### 4. jQuery/DataTables

```typescript
const table = ($('#dataTable') as JQuery).DataTable({...});
```

## Deduplicação

- 601 toast patterns → `ERPBootstrap.showToast()`
- 420 guard patterns → `ERPBootstrap.checkPermission()`
- 204 Window augmentation patterns → centralizado em `globals.d.ts`
