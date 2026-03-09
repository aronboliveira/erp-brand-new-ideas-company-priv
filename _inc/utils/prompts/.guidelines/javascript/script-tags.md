# Blade Script Tag Rewriting Pattern

## Overview

Blade templates in the ERP application frequently contain inline
`<script>` blocks with legacy JavaScript. This guide describes the
standard pattern for rewriting those blocks into modern, defensive,
ES6+ code.

---

## Core Principles

### 1. ES6+ Syntax

All rewritten script tags must use modern JavaScript:

- `const` / `let` (never `var`)
- Arrow functions for callbacks
- Template literals
- Optional chaining (`?.`) and nullish coalescing (`??`)
- Destructuring where appropriate

### 2. Defensive Programming

Never assume a DOM element exists. Guard every query:

```js
const el = document.getElementById("target");
if (!el) return;
```

### 3. Toast / Alert Fallback

When displaying messages to users, prefer the singleton toast system.
If the singleton is unavailable (e.g., a standalone auth page), fall
back to `alert()`:

```js
const toast = window.erpGuard?.showToast;
if (toast) toast("error", msg);
else alert(msg);
```

### 4. IIFE Wrapping

Every rewritten `<script>` body is wrapped in an IIFE with a try/catch:

```js
(function erpPageName() {
  try {
    // …body…
  } catch (e) {
    console.error("[erpPageName]", e);
  }
})();
```

---

## Translation Dictionary Pattern

The ERP uses `window.translations` — a nested object keyed by language
code — to provide client-side translations without an additional HTTP
request.

### Structure

```js
// Set in Blade (rendered by PHP)
window.translations = {
  en: { "task.created": "Task created successfully", /* … */ },
  "pt-br": { "task.created": "Tarefa criada com sucesso", /* … */ },
};
```

### Resolution Algorithm

```js
const errFb = "# ERROR";
const dataClientLocalized = "data-client-localized";
const dataGuardMsg = "data-guard-msg";
let msg = errFb;

if (
  el.getAttribute("data-sv-localized") === "true" ||
  el.getAttribute(dataClientLocalized) === "true"
)
  msg = el.getAttribute(dataGuardMsg) || errFb;
else {
  let lang = (
    window.sessionStorage.getItem("erp-np-lang") ||
    document.documentElement.lang ||
    "en"
  )
    .toLowerCase()
    .replace(/_/g, "-");
  lang = lang === "pt-br" ? lang : lang.slice(0, 2);
  const msgKey = /* context-specific key */;
  msg =
    window.translations?.[lang]?.[msgKey] ||
    el.getAttribute(dataGuardMsg) ||
    window.translations?.["en"]?.[msgKey] ||
    errFb;
  if (msg !== errFb) {
    el.setAttribute(dataGuardMsg, msg);
    el.setAttribute(dataClientLocalized, "true");
  }
}
```

### Key Points

- `data-sv-localized="true"` means the server already set the message.
- `data-client-localized="true"` means JS already resolved it once.
- Language detection: `sessionStorage > <html lang> > "en"`.
- Portuguese Brazilian is the only multi-segment tag kept (`pt-br`);
  everything else is truncated to two characters.
- Fallback chain: `translations[lang][key] → data-guard-msg attribute
  → translations["en"][key] → "# ERROR"`.

---

## MutationObserver Cleanup

When a script tag creates a `MutationObserver`, the observer **must**
be disconnected when the target element is removed from the DOM. Use a
parent observer or an `AbortController`-like pattern:

```js
const observer = new MutationObserver(mutations => {
  for (const m of mutations) {
    if (!document.contains(targetEl)) {
      observer.disconnect();
      return;
    }
    // …handle mutations…
  }
});
observer.observe(targetEl, { childList: true, subtree: true });
```

---

## Route Verification

Before executing page-specific logic, verify the current route matches
the expected page. This prevents scripts from running on unrelated
pages when Blade caching or Turbolinks are active:

```js
const expectedPath = "/admin/tasks";
if (!window.location.pathname.startsWith(expectedPath)) return;
```

---

## Deferred / Async Loading Strategy

- Route-specific scripts: `defer` attribute so they run after DOM parse.
- Third-party libraries not needed for first paint: `async`.
- Critical path scripts (singleton layer): `defer`, loaded in order.
- Inline `<script>` tags in Blade: rewrite to external files when
  they exceed ~30 lines.
