# JavaScript / TypeScript Coding Rules

> Authoritative natural-language reference for the 19+ JS/TS coding rules
> enforced across the ERP Prestech codebase.

---

## 1. DRY — Don't Repeat Yourself

### setAttribute batches

When three or more **consecutive** `setAttribute` calls target the same
element, replace them with an `Object.entries` loop:

```js
// ❌ Before
el.setAttribute("role", "alert");
el.setAttribute("aria-live", "polite");
el.setAttribute("data-visible", "true");

// ✅ After
Object.entries({
  role: "alert",
  "aria-live": "polite",
  "data-visible": "true",
}).forEach(([k, v]) => el.setAttribute(k, v));
```

The same principle applies to **property assignments** — three or more
sequential assignments to the same object should use `Object.assign` or
a spread-based helper.

---

## 2. Cached String Literals

Any string literal that appears **two or more times** in the same file must
be extracted into a named `const`:

```js
const dataGuardMsg = "data-guard-msg";
el.getAttribute(dataGuardMsg);
el.setAttribute(dataGuardMsg, msg);
```

Cross-file shared strings belong in a `.constants.ts` module exported with
`Object.freeze({} as const)`.

---

## 3. Minimal JSDoc

JSDoc is **required** on:

- Function declarations (`function foo() {}`)
- Named function expressions (`const foo = function bar() {}`)

JSDoc is **not required** on:

- Arrow-function callbacks passed inline
- One-liner utility arrows whose name is self-documenting

**Never** repeat TypeScript types inside `@param` / `@returns` tags — the
compiler already knows.

---

## 4. Blank-Line Discipline

- **No extra blank lines** between related statements inside a block.
- **One blank line** between top-level function/class declarations.
- **One blank line** after the import block.

---

## 5. Collapsed Declarations

Merge up to **three** sequential `const` (or `let`) declarations with a
trailing-comma separator — unless any of them use complex destructuring:

```js
// ✅ Collapsed
const a = 1,
  b = 2,
  c = 3;

// ✅ Keep separate — complex destructuring
const { x, y } = coords;
const [head, ...tail] = list;
```

---

## 6. Bracketless One-Liners & Short-Circuiting

- Single-statement `if` / `else` → omit curly braces.
- Guard + single call → short-circuit with `&&`:
  ```js
  el && el.focus();
  ```
- Symmetric value branches → ternary:
  ```js
  const label = isNew ? "Create" : "Update";
  ```
- Avoid ternary whose `:` branch is nullish in a standalone expression
  (triggers `no-unused-expressions`).

---

## 7. IIFE Try/Catch Wrapping

Every route-level script is wrapped in an IIFE with a single `try/catch`
covering the entire body. The `catch` block logs via `console.error` using a
module-name template:

```js
(function erpModuleName() {
  try {
    // …entire body…
  } catch (e) {
    console.error("[erpModuleName]", e);
  }
})();
```

---

## 8. DOM Query Performance Preference

Use the fastest API that satisfies the lookup. Preference order:

| Rank | API                            | Notes                  |
| ---- | ------------------------------ | ---------------------- |
| 1    | `getElementById`               | Fastest — unique IDs   |
| 2    | `getElementsByClassName`       | Live HTMLCollection    |
| 3    | `getElementsByTagName`         | Live HTMLCollection    |
| 4    | `HTMLFormElement.elements`     | Use `form.elements` and `namedItem()` for form fields |
| 5    | `querySelector`                | CSS selector — single  |
| 6    | `querySelectorAll`             | CSS selector — list    |

---

## 9. ES6+ Target

Always write modern JavaScript:

- Arrow functions for callbacks and nested closures
- Template literals instead of string concatenation
- `const` / `let` — never `var`
- Destructuring (object & array)
- Spread / rest operators
- Optional chaining (`?.`)
- Nullish coalescing (`??`)
- `Object.entries`, `Array.from`, `for…of`

---

## 10. Browser Support

Target **Chromium** and **Gecko** (Firefox) engines. Avoid WebKit-only
features or APIs unless a polyfill is bundled.

---

## 11. Testing

After every meaningful change run:

```bash
jest          # unit tests
playwright    # e2e / integration
eslint .      # lint
tsc --noEmit  # type check
```

---

## 12. Cached Regular Expressions

Declare regular expressions at **scope level** (module or outer function),
never inside loops or hot paths:

```js
const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
```

---

## 13. Sequential Same-Target Calls → Iterative

Consecutive calls targeting the same object should be combined:

| Pattern               | Refactored form                        |
| --------------------- | -------------------------------------- |
| Multiple `appendChild`| `for…of` over a pairs/fragment array   |
| Multiple `style.*`    | `Object.assign(el.style, { … })`      |
| jQuery `removeClass`  | Space-separated class string           |

---

## 14. Event-Listener Guards

Before calling `addEventListener`, check
`el.dataset.<listenerName>Bound`. After binding, set it to `"1"`:

```js
if (!el.dataset.submitBound) {
  el.addEventListener("submit", handler);
  el.dataset.submitBound = "1";
}
```

---

## 15. Inline Single-Use Variables

If a variable is used **only once** and its name does not add significant
clarity, inline it:

```js
// ❌
const url = resolveUrl("/api/tasks");
safeFetch(url);

// ✅
safeFetch(resolveUrl("/api/tasks"));
```

---

## 16. Vendor File Marking

Non-project files (libraries, third-party scripts) must have as their
**first non-empty line**:

```js
// # ! VENDOR FILE — DO NOT EDIT
```

They must also be **excluded** from `tsconfig.json` and
`eslint.config.mjs`.

---

## 17. ESLint Migration Config

During the JS → TS migration the following rules are **disabled**:

- `@typescript-eslint/strict-boolean-expressions`
- `@typescript-eslint/no-unnecessary-condition`

`varsIgnorePattern` is configured to ignore common globals:
`jQuery|\\$|bootstrap|feather|SimpleBar|dragula`.

---

## 18. Async Return Types

| Signature        | Return annotation         |
| ---------------- | ------------------------- |
| `async function` | `Promise<void>`           |
| Non-async        | `: void`                  |
| Complex inferred | `// eslint-disable-next-line` with reason |

---

## 19. eslint-disable-next-line

- **Never** place inside template literals.
- **Combine** multiple rule names on a single directive.
- **Add a reason** comment when the suppression is non-obvious.
- **Prefer** fixing the root cause over suppressing.

---

## 20. Variable Assignment Discipline

- Don't assign variables used only once — inline them.
- Chain `const` / `let` with trailing commas when declarations are simple.
- Flag constant reassignment with `// * Constant reassignment`.
- Flag `var` usage — suggest `const` or `let` replacement.
- Comment out unused variables (preserve ordering) and flag with
  `// * Unused variable`.
- Replace pseudo-hex-code variable names (`x1A3F`) with semantic names.

---

## 21. Statement Simplification

- Remove single-statement curly brackets (bracketless one-liners).
- Avoid ternary whose `:` ends in a nullish value in standalone
  expressions (triggers `no-unused-expressions`).
- Use `&&` for simple direct-expression chaining instead of `if()`.
- Replace dummy statements (`!![]`, `!0`) with `true` (or remove entirely).

---

## 22. forEach vs for…of

- Use `forEach` **only** when already chaining (e.g., `arr.filter().forEach()`),
  **and** the operation is not expensive.
- Prefer `for…of` in all other cases.
- For extremely expensive DOM operations, use a traditional C-style
  `for` loop for performance.

---

## 23. Function Declaration Scope

- **Global / module scope** → function declarations
  (`function foo() {}`).
- **Nested / callback** → arrow functions (`const fn = () => {}`).

---

## 24. HTTP Response Handling

Never destructure an HTTP response immediately. Always test the `res`
variable first:

```js
const res = await axios.get("/api/data");
if (!res || res.status !== 200) {
  guard.error("Request failed");
  return;
}
const { data } = res;
```

---

## 25. Arrow Function Parentheses

Remove parentheses for a **single untyped parameter**:

```js
// ❌
items.map((x) => x.id);

// ✅
items.map(x => x.id);
```

(In TypeScript with a type annotation, parentheses are required.)

---

## 26. Always axios, Never fetch

Use **axios** for all HTTP calls. Never use the native `fetch` API.

---

## 27. API Call Structure

- Write each fetch/axios call inside its own `try/catch`.
- Name the wrapper function by `<endpoint><Method>`:
  e.g., `tasksGet()`, `invoicePost()`.
- Place API call functions in a **separate module or block**
  (`api/*.ts` or a clearly delineated section).

```js
// api/tasks.ts
export async function tasksGet(): Promise<void> {
  try {
    const res = await axios.get(resolveUrl("/api/tasks"));
    if (!res || res.status !== 200) return guard.error("Failed");
    // …process res.data…
  } catch (e) {
    console.error("[tasksGet]", e);
  }
}
```
