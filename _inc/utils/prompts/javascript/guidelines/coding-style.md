# JavaScript / TypeScript Coding Style Guidelines

> Lax, natural-language guide for ERP Prestech front-end code.
> For strict, machine-parseable rules see the sibling `.yml`, `.xml`, and `.json` files.

---

## 1. DRY Repeated Attribute / Property Assignments

When three or more consecutive `setAttribute()` calls target the **same element**, convert them to a loop over key–value pairs:

```ts
// BEFORE
overlay.setAttribute("role", "dialog");
overlay.setAttribute("aria-modal", "true");
overlay.setAttribute("aria-labelledby", "diagTitle");
overlay.setAttribute("aria-describedby", "diagDesc");

// AFTER
for (const [k, v] of Object.entries({
  role: "dialog",
  "aria-modal": "true",
  "aria-labelledby": "diagTitle",
  "aria-describedby": "diagDesc",
}))
  overlay.setAttribute(k, v);
```

The same principle applies to direct property assignments:

```ts
// BEFORE
img.src = "/assets/images/406-art.webp";
img.alt = "Illustration for error/406";
img.className = "img-fluid rounded";

// AFTER
for (const [k, v] of Object.entries({
  src: "/assets/images/406-art.webp",
  alt: "Illustration for error/406",
  className: "img-fluid rounded",
}))
  (img as Record<string, unknown>)[k] = v;
```

If a block mixes `setAttribute` and direct assignments, **reorder** lines so each group is contiguous, and apply the matching loop pattern.

---

## 2. Cache Repeated String Literals

Strings used more than twice in a file (e.g. icon markup) **must** be extracted into a `const`:

```ts
const infoIcon = '<i class="bi bi-info-circle" aria-hidden="true"></i>';
```

This makes future edits propagate automatically and keeps the bundle slightly smaller.

---

## 3. Minimal JSDoc

Add JSDoc for every **function declaration** and **named function expression**. Keep it minimal — don't repeat what TypeScript already tells the reader through types.

```ts
/** Initialises the attendance bulk-import form listeners. */
function initBulkImport(): void { … }
```

For short lambdas / inline callbacks you may omit JSDoc if intent is obvious from context.

---

## 4. No Extra Blank Lines Between Related Statements

Remove blank lines between guard clauses, variable declarations, and closely related logic:

```ts
// BEFORE
if (!targetElement) return;

const content = targetElement.innerHTML;
const m = content.match(/^[\s\n\t\r]*[0-9]+</);

if (!m) return;

// AFTER
if (!targetElement) return;
const content = targetElement.innerHTML;
const m = content.match(/^[\s\n\t\r]*[0-9]+</);
if (!m) return;
```

---

## 5. Collapse Sequential Declarations

Two to three consecutive `const` (or `let` / `var`) assignments should be merged using the comma operator:

```ts
// BEFORE
const content = targetElement.innerHTML;
const m = content.match(/^[\s\n\t\r]*[0-9]+</);

// AFTER
const content = targetElement.innerHTML,
  m = content.match(/^[\s\n\t\r]*[0-9]+</);
```

Do **not** merge if readability would suffer (e.g., complex destructuring).

---

## 6. Bracketless One-Liners

Single-statement `if`/`else` blocks must drop curly braces. Prefer short-circuit or ternary when it's more expressive:

```ts
// Option A — short-circuit for guard + single call
!isModal && document.body.classList.add("overflow-hidden");

// Option B — ternary for symmetric branches
document.readyState === "loading"
  ? document.addEventListener("DOMContentLoaded", checkMounted)
  : checkMounted();
```

---

## 7. IIFE Try/Catch Wrapping

Every IIFE that starts a `.ts` route/page file must contain a top-level `try { … } catch (e) { … }` around the full body. The `catch` should issue a clean diagnostic:

```ts
((): void => {
  try {
    // … all module code …
  } catch (e) {
    console.error(`[module-name] failed to initialise:`, e);
  }
})();
```

This prevents a single broken module from crashing the entire client.

---

## 8. Prefer Performant DOM Queries

Use the **cheapest** DOM query method that applies. Ranked fastest → slowest:

1. `document.getElementById(id)` — fastest
2. `document.getElementsByClassName(cls)` / `document.getElementsByTagName(tag)` — live HTMLCollection
3. `HTMLFormElement.elements` / `HTMLFormControlsCollection.namedItem()` — for form fields
4. `document.querySelector()` / `document.querySelectorAll()` — only when CSS selectors are truly needed

**Do not** swap blindly — only switch when it demonstrably improves performance (measure first).

---

## 9. Observers

`MutationObserver` and `IntersectionObserver` **can** be useful, but are never mandatory. Only add them when they provide a clear benefit over simpler approaches (event listeners, polling, etc.).

---

## 10. General

- Target **ES6+** syntax (arrow functions, template literals, `const`/`let`, destructuring, etc.).
- Support **Chromium** and **Gecko** (Firefox) engines.
- Always run `jest + playwright + eslint + tsc` after changes to verify nothing regresses.
- When choosing between two implementation approaches, **benchmark first** — pick the more performant one.

---

## 11. Cache Hard-Coded Strings & RegExps

String literals used **2 or more** times in a file must be stored in a `const` at the nearest common scope.
The same applies to `RegExp` instances — cache them to avoid re-compilation on each call:

```ts
// BEFORE
const m = html.match(/^[\s\n\t\r]*[0-9]+</);

// AFTER (at top of scope)
const NUM_PREFIX_RE = /^[\s\n\t\r]*[0-9]+</;
// ... later ...
const m = html.match(NUM_PREFIX_RE);
```

Strings shared **across multiple files** belong in a shared `.constants.ts` inside a deep-frozen Record:

```ts
// .constants.ts
export const SHARED = Object.freeze({
  SEL_CSRF_META: 'meta[name="csrf-token"]',
} as const);
```

---

## 12. Convert Sequential Procedures to Iterative

Repetitive sequential calls (e.g. `appendChild`, `.style.*`, `.removeClass()`) must be converted to loops or combined calls:

```ts
// BEFORE — sequential appendChild
card.appendChild(cardBody);
overlay.appendChild(card);
targetElement.appendChild(overlay);

// AFTER — iterative
for (const [p, c] of [
  [card, cardBody],
  [overlay, card],
  [targetElement, overlay],
] as [Node, Node][])
  p.appendChild(c);

// BEFORE — sequential style
overlay.style.zIndex = "2147483000";
overlay.style.background = "rgba(0,0,0,.25)";

// AFTER — Object.assign
Object.assign(overlay.style, {
  zIndex: "2147483000",
  background: "rgba(0,0,0,.25)",
});

// BEFORE — sequential jQuery removeClass
$tp.removeClass("bg-warning");
$tp.removeClass("bg-primary");
$tp.removeClass("bg-success");

// AFTER — combined
$tp.removeClass("bg-warning bg-primary bg-success");
```

---

## 13. Guard Event Listeners with Dataset Checks

Before adding an `addEventListener`, **always** check if the element already has a similar listener bound using a `dataset` pseudoboolean:

```ts
// BEFORE
slider.addEventListener("mousedown", handler);

// AFTER
if (slider.dataset.hscrollerBound) return;
slider.dataset.hscrollerBound = "1";
slider.addEventListener("mousedown", handler);
```

This prevents duplicate registrations when scripts re-execute or modules re-initialise.

---

## 14. Inline Single-Use Values

If a variable is assigned and used **only once**, remove the variable and use the value directly — unless the name significantly improves readability:

```ts
// BEFORE
const content = targetElement.innerHTML;
const m = content.match(NUM_PREFIX_RE);

// AFTER
const m = targetElement.innerHTML.match(NUM_PREFIX_RE);

// BEFORE
const _dataTable = new DataTable(".datatable");

// AFTER — side effect only
new DataTable(".datatable");
```

---

## 15. Vendor File Marking

All third-party / vendor / bundle files that live inside `ts/src/` but should **not** be edited must carry a header comment on the first non-empty line:

```ts
// # ! VENDOR FILE — DO NOT EDIT
```

These files are also excluded from ESLint and `tsc` via their respective config `ignores` / `exclude` arrays. Currently marked vendor files:

- `public/assets/js/vendor-all.ts`
- `public/js/cookieconsent.ts`
- `public/js/site.ts`
- `public/js/app.ts`
- `public/Modules/landingpage/js/vendor-all.ts`
- `public/Modules/landingpage/js/app.ts`

When adding a **new** vendor file, always:

1. Add the `// # ! VENDOR FILE` header.
2. Add the path to `tsconfig.json` → `exclude`.
3. Add the glob to `eslint.config.mjs` → `ignores`.

---

## 16. ESLint Configuration Rationale (Migration Phase)

During the JS → TS migration the following rules are intentionally **disabled** in `eslint.config.mjs`:

| Rule                                            | Reason                                                                                                                                     |
| ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| `@typescript-eslint/strict-boolean-expressions` | ~1 100 false positives from defensive `if (el)` / `if (str)` null-guards that are idiomatic in DOM code.                                   |
| `@typescript-eslint/no-unnecessary-condition`   | ~1 300 false positives from truthy checks on values TypeScript narrows to non-nullable after assignment but that may be `null` at runtime. |

Additional config notes:

- `varsIgnorePattern` includes `^_|^\\$|^jQuery|^bootstrap|^feather|^SimpleBar|^dragula` to suppress warnings on intentionally unused ambient globals.
- `caughtErrorsIgnorePattern: ".*"` — catch-block variables are often logged inline and the binding name itself is not used standalone.

> **Re-enable these rules** after all JS-era patterns have been fully retyped/narrowed. Track with issue / task.

---

## 17. Async Function Return Types

Async and non-async functions have different return type rules:

```ts
// ✅ Non-async IIFE returning nothing → : void
((): void => { … })();

// ✅ Async IIFE returning nothing → : Promise<void>
(async (): Promise<void> => { … })();

// ❌ NEVER annotate an async function as : void
(async (): void => { … })();   // tsc error TS1064

// ✅ Functions that return a value — let TypeScript infer, add eslint-disable
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
function getMsg(key: string) { return messages[key] ?? key; }
```

Key rules:

- **`async` functions** must use `Promise<void>` (never bare `void`).
- **Functions returning a value**: if the inferred type is complex or union-heavy, prefer `// eslint-disable-next-line @typescript-eslint/explicit-function-return-type` over an incorrect annotation.
- **Non-async void functions** (IIFEs, event handlers): annotate `: void` explicitly.

---

## 18. `eslint-disable` Patterns

Use `eslint-disable-next-line` sparingly, and **only** for cases where the ESLint rule genuinely cannot be satisfied without worse code:

```ts
// Acceptable — inferred return is complex union from JS-era code
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
function buildPayload(data: unknown) { … }

// Acceptable — uninitialized let re-assigned in branching logic
// eslint-disable-next-line prefer-const
let slider: Slider;
```

Rules:

1. **Never** place `// eslint-disable-next-line` inside template literals or multi-line strings — it becomes literal text, not a directive.
2. When multiple rules need disabling on the same line, combine them in a single comment:
   ```ts
   // eslint-disable-next-line @typescript-eslint/no-unused-vars, @typescript-eslint/explicit-function-return-type
   ```
3. Always add a brief reason if the disable is non-obvious.
4. Prefer fixing the root cause over disabling.

---

## 19. TypeScript Type Declaration Organization

Type declarations (interfaces, types, declares) must be **separated from implementation code** and placed in dedicated `.d.ts` files under `ts/src/declarations/`.

### Directory Structure

```
ts/src/declarations/
├── tests/
│   └── e2e.interfaces.d.ts          # E2E test interfaces
├── pages/
│   ├── datepicker.d.ts              # Datepicker/DateRangePicker types
│   └── form-validation.d.ts         # Bouncer form validation types
└── routes/
    ├── fullcalendar.interfaces.d.ts # FullCalendar calendar types
    ├── dragula.interfaces.d.ts      # Dragula drag-and-drop types
    ├── jquery-ui.interfaces.d.ts    # jQuery UI/plugin extensions
    ├── datatables.interfaces.d.ts   # DataTables extensions
    ├── ajax-responses.interfaces.d.ts # AJAX response interfaces
    ├── vendor-libs.d.ts             # Global augmentations (Window, JQuery)
    └── vendor-libs-ambient.d.ts     # Ambient declares for vendor globals
```

### File Naming Conventions

- `.interfaces.d.ts` — for `interface` definitions
- `.types.d.ts` — for `type` aliases
- `.d.ts` — for mixed or ambient declarations
- Suffixes are optional but recommended for clarity

### Import Patterns

```ts
// Type-only imports (no runtime effect)
import type { FullCalendarInstance } from "../declarations/routes/fullcalendar.interfaces";

// Ambient declarations are auto-included via tsconfig.json
// No import needed for globals like Datepicker, Bouncer, dragula, html2pdf
```

### Rules

1. **Never define interfaces/types inline** in route/page `.ts` files — extract to declarations.
2. **Ambient declares** (for globals like `Datepicker`, `Bouncer`, `html2pdf`) go in `vendor-libs-ambient.d.ts`.
3. **Global augmentations** (`declare global { interface Window { ... } }`) go in `vendor-libs.d.ts`.
4. **Module-specific types** (e.g. AJAX response shapes) go in grouped files like `ajax-responses.interfaces.d.ts`.
5. **Derived types** like `type RoleKey = keyof typeof RoleTemplates` that depend on runtime constants may remain inline.

### tsconfig.json Configuration

The `ts/src/declarations/` folder is included in `tsconfig.json`:

```jsonc
{
  "include": [
    // ...
    "ts/src/declarations/**/*.d.ts",
  ],
}
```

This ensures all ambient declarations are available globally without explicit imports.
