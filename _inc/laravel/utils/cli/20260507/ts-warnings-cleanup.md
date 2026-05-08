# `ts/` ESLint warnings cleanup — 2026-05-07

Follow-up to [`ts-eslint-cleanup.md`](ts-eslint-cleanup.md) (errors phase).
After clearing all 1,112 errors, 63 warnings remained. Per user direction
("fix all of them BUT the ones clustered into 'migration noise'"), this
pass addresses the 34 meaningful ones and leaves 29 migration-noise warnings
untouched.

## Before
```bash
cd _inc/laravel/ts
npx eslint .
# → 63 problems (0 errors, 63 warnings)
```

## Triage

### Meaningful (34) — fixed in this pass
| Rule | Count | Nature |
|---|---:|---|
| `@typescript-eslint/no-unused-vars` | 8 | Genuinely dead imports / consts |
| `@typescript-eslint/no-floating-promises` | 4 | Promise discarded without `void`/`await` |
| `@typescript-eslint/no-misused-promises` | 7 | `async` handler passed where `void` callback expected |
| `@typescript-eslint/prefer-nullish-coalescing` | 15 | `||` between `Element \| null` (or `Record \| undefined`) — semantically equivalent to `??`, but `??` is the safer default |

### Migration noise (29) — left as-is
| Rule | Count | Reason to defer |
|---|---:|---|
| `@typescript-eslint/explicit-function-return-type` | ~13 | Generated migration; covered by inline `// eslint-disable-next-line` already; cosmetic |
| `@typescript-eslint/no-unsafe-*` | ~14 | Unavoidable until the global `window.*` ambient typings are tightened (separate workstream) |
| `@typescript-eslint/no-explicit-any` | ~2 | Same — bound to ambient `Window` declarations |

## Fixes applied (27 files)

### `no-unused-vars` (8)
- `src/public/assets/js/core/erp-bootstrap.ts:24` — removed `const TOAST_DELAY = 4000;`
- `src/public/assets/js/routes/holidays/calendar.ts:8` — removed `FullCalendarInstance` type import
- `src/public/assets/js/routes/tasks/calendar.ts:8` — same
- `src/public/assets/js/routes/zoomMeetings/calendar.ts:8` — same
- `src/public/assets/js/routes/stages/reorder.ts:8` — removed `JQuerySortableUI` type import
- `src/public/assets/js/routes/tasks/drag.ts:7-12` — type-only import block replaced with side-effect import (the file uses none of the imported names directly)

### `no-floating-promises` (4)
- `src/public/assets/js/routes/invoices/clipboard.ts:8` — `void` prefix on top-level promise
- `src/public/assets/js/routes/ai/generate/clipboard.ts:75,92` — `void doCopy(...)`
- `src/public/assets/js/routes/ai/grammar/clipboard.ts:73` — same

### `no-misused-promises` (7)
Pattern for event handlers: wrap async body in a sync `void` shim so the
listener type matches `(e: Event) => void`:
```ts
addEventListener("change", () => void (async (): Promise<void> => { ... })());
```
- `src/public/assets/js/routes/bills/select.ts:19`
- `src/public/assets/js/routes/bills/editSelect.ts:15`
- `src/public/assets/js/routes/bills/vendorEditSelect.ts:19`
- `src/public/assets/js/routes/expenses/create.ts:301,307,313` — `() => fetchDetail(...)` → `() => { void fetchDetail(...); }`
- `src/public/assets/js/routes/expenses/url.ts:59-93` — refactored WeakMap handler-store: `asyncHandler` retained for body, sync `handler` is what's stored + bound

### `prefer-nullish-coalescing` (15)
14 sites are the same shape — `qs(...) || qs(...)` between two
`Element | null` lookups (querySelector chain). Both branches can only
be `Element` or `null`, so `||` and `??` produce identical results, but
`??` is the lint-preferred form. The inline disable comment is no longer
needed and was removed:

```ts
// before
!!(
  // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
  (
    qs('link[rel="stylesheet"][href*="bootstrap"]') ||
    qs('link[href*="bootstrap"]')
  )
) && !!window.bootstrap.Toast;

// after
!!(
  qs('link[rel="stylesheet"][href*="bootstrap"]') ??
  qs('link[href*="bootstrap"]')
) && !!window.bootstrap.Toast;
```

Sites:
- `expenses/ship.ts:25`
- `holidays/calendar.ts:33`
- `installer/dismiss.ts:25`
- `installer/env.ts:24`
- `leads/list.ts:28`
- `leads/order.ts:30`
- `payslips/storeEnv.ts:24`
- `plans/paymentWall.ts:25`
- `transactions/pdf.ts:26`
- `users/profiles/scroll.ts:27`
- `vendors/copy.ts:27`
- `warehouses/transfers/quantity.ts:27`
- `zoomMeetings/actions.ts:31`
- `zoomMeetings/calendar.ts:32`

15th site — `pos/lang/cart.ts:14`:
```ts
// before
window.translations = window.translations || {};
// after
window.translations = window.translations ?? {};
```
`window.translations` is typed `Record<string, Record<string, string>> | undefined`,
so no falsy-but-not-nullish object can exist; behavior unchanged.

## After
```bash
cd _inc/laravel/ts
npx tsc --noEmit
# → clean (exit 0)

npx eslint .
# → 29 problems (0 errors, 29 warnings)
```

29 remaining warnings are all in the migration-noise tier listed above.
They will be tackled in a separate pass when the ambient `Window` typings
are tightened.
