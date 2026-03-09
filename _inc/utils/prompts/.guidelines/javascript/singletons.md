# ERPGuard / ERPUtils / ERPBootstrap — Singleton Architecture

## Why This Exists

The ERP codebase contains **1 100+ route-level JavaScript files** that each
independently implement the same boilerplate functions:

- `getMsg(el, key)` — resolve a translated validation/error message
- `showToast(type, msg)` — show a toast notification (success / error / warning / info)
- `getCsrfToken()` — pull the CSRF token from the `<meta>` tag
- `safeFetch(url, opts)` — axios wrapper with error handling

This produced roughly **60 KB of duplicated code** across the application.
The singleton layer eliminates this duplication by centralising the
boilerplate into three shared files.

---

## The Three Singleton Files

### 1. `erp-guard.js` — IIFE + Static Instance

```
public/js/erp-guard.js
```

Pattern: **IIFE that attaches a frozen singleton to `window.erpGuard`.**

```js
(function () {
  if (window.erpGuard) return;          // idempotent
  const guard = Object.freeze({ /* … */ });
  window.erpGuard = guard;
})();
```

The instance is created once and never replaced. All route files access it
via `const guard = window.erpGuard`.

### 2. `erp-utils.js` — Constructor-Check Singleton

```
public/js/erp-utils.js
```

Pattern: **Class with a static `instance` check in the constructor.**

```js
class ERPUtils {
  constructor() {
    if (ERPUtils.instance) return ERPUtils.instance;
    // …init…
    ERPUtils.instance = this;
  }
}
window.erpUtils = new ERPUtils();
```

Provides utility methods that don't fit the "guard" metaphor (DOM helpers,
formatting, date utilities, etc.).

### 3. `erp-bootstrap.js` — Plain IIFE Registry

```
public/js/erp-bootstrap.js
```

Pattern: **IIFE that exposes a `register` / `require` registry.**

```js
(function () {
  const _registry = {};
  window.ERPBootstrap = Object.freeze({
    register(key, factory) { _registry[key] = factory; },
    require(...keys) {
      return keys.map(k => {
        if (!_registry[k]) throw new Error(`[ERPBootstrap] missing: ${k}`);
        return typeof _registry[k] === "function" ? _registry[k]() : _registry[k];
      });
    },
    ensureSingletons() { /* verify all expected keys are present */ },
  });
})();
```

Route files pull exactly the singletons they need:
```js
const [guard, utils] = ERPBootstrap.require("guard", "utils");
```

---

## ERPGuard Public API

| Method              | Purpose                                          |
| ------------------- | ------------------------------------------------ |
| `getMsg(key)`       | Resolve a translated message from the dictionary |
| `showToast(type, msg)` | Display a toast notification                  |
| `error(msg)`        | `showToast("error", msg)`                        |
| `warning(msg)`      | `showToast("warning", msg)`                      |
| `success(msg)`      | `showToast("success", msg)`                      |
| `info(msg)`         | `showToast("info", msg)`                         |
| `confirm(msg, cb)`  | Show a confirmation dialog, call `cb` on accept  |
| `getCsrfToken()`    | Read CSRF token from `<meta name="csrf-token">`  |
| `resolveUrl(path)`  | Prepend the app base URL to a relative path      |
| `safeFetch(url, opts)` | axios wrapper with try/catch + error toast    |
| `ajaxPost(url, data)` | POST shorthand via safeFetch                   |
| `ajaxDelete(url)`   | DELETE shorthand via safeFetch                   |
| `isInvalidUrl(url)` | Basic URL validation                             |
| `bindSubmitGuard(form)` | Prevent double-submit on a `<form>`          |

---

## ERPBootstrap API

| Method                | Purpose                                        |
| --------------------- | ---------------------------------------------- |
| `register(key, factory)` | Register a singleton factory or instance    |
| `require(...keys)`    | Retrieve registered singletons by key          |
| `ensureSingletons()`  | Assert that all expected keys are registered   |

### Default Registry Entries

| Key       | Resolves to         |
| --------- | ------------------- |
| `guard`   | `window.erpGuard`   |
| `utils`   | `window.erpUtils`   |

---

## Blade Integration

The singleton scripts are loaded with `defer` in three layout files:

1. **`resources/views/layouts/admin.blade.php`** — admin panel
2. **`resources/views/layouts/auth.blade.php`**  — authentication pages
3. **`resources/views/layouts/landing.blade.php`** — landing page

```html
<script src="{{ asset('js/erp-guard.js') }}" defer></script>
<script src="{{ asset('js/erp-utils.js') }}" defer></script>
<script src="{{ asset('js/erp-bootstrap.js') }}" defer></script>
```

---

## File Categorization

When auditing route files for refactoring, classify each as:

| Category | Description                                                   | Action                                      |
| -------- | ------------------------------------------------------------- | ------------------------------------------- |
| **Heavy**    | Contains all 4 boilerplate functions (getMsg, showToast, getCsrfToken, safeFetch) | Full delegation to guard.*              |
| **Medium**   | Contains only `getMsg` or a subset                        | Partial delegation                          |
| **Light**    | Already clean — no boilerplate duplication                 | No change needed                            |
| **Namespace** | Exports a public API (used by other files)                | Preserve exports, delegate internally       |

---

## Route File Shape Post-Refactor

A refactored route file should look like:

```js
(function erpTaskIndex() {
  try {
    const [guard] = ERPBootstrap.require("guard");

    const form = document.getElementById("taskForm");
    if (!form) return;

    if (!form.dataset.submitBound) {
      form.addEventListener("submit", async e => {
        e.preventDefault();
        guard.bindSubmitGuard(form);
        const res = await guard.safeFetch(guard.resolveUrl("/api/tasks"), {
          method: "POST",
          body: new FormData(form),
        });
        if (!res || res.status !== 200) return guard.error("Failed");
        guard.success("Task created");
      });
      form.dataset.submitBound = "1";
    }
  } catch (e) {
    console.error("[erpTaskIndex]", e);
  }
})();
```

---

## Namespace Module Pattern

For files that export a public API consumed by other scripts, refactoring
must **preserve all exported symbols**. Only the internal implementation
delegates to the singleton:

```js
// Before (namespace module)
window.TaskUtils = {
  getMsg(el, key) { /* duplicated logic */ },
  formatDate(d) { /* unique logic */ },
};

// After
(function () {
  const [guard] = ERPBootstrap.require("guard");
  window.TaskUtils = {
    getMsg: (el, key) => guard.getMsg(key),   // delegate
    formatDate(d) { /* unique logic kept */ }, // preserve
  };
})();
```

All callers of `TaskUtils.getMsg(el, key)` continue to work — only the
internal path changes.
