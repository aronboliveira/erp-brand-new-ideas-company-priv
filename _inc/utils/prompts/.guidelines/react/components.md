# React Component Rules

## Overview

These rules govern React component development in the ERP Prestech
codebase, covering hooks, naming conventions, performance patterns,
and framework integration.

---

## 1. Hook Dependency Arrays

Be **mindful** of dependency arrays in `useEffect`, `useMemo`, and
`useCallback`:

- **Don't include** reassigned variables (e.g., a `let` counter that
  changes in the same render cycle).
- **Don't include** batched dispatch variables (when multiple dispatches
  fire synchronously, React batches them — the intermediate values are
  stale in deps).
- Use `useMemo` for expensive calculations:
  ```tsx
  const sorted = useMemo(() => items.sort(compareFn), [items]);
  ```
- Use `useCallback` for functions passed as props or added to dependency
  arrays:
  ```tsx
  const handleClick = useCallback(() => {
    /* … */
  }, [dependency]);
  ```

---

## 2. Component Naming from Blade Transpilation

When transpiling a Blade view to a React component, derive the name
from the Blade path using PascalCase:

| Blade path                  | Component name   |
| --------------------------- | ---------------- |
| `admin/page.blade.php`      | `AdminPage`      |
| `employee/create.blade.php` | `EmployeeCreate` |
| `invoice/edit.blade.php`    | `InvoiceEdit`    |

If the path doesn't clearly suggest a name, mark with:

```tsx
// ? NO CLEAR NAME — derived from <original-path>
```

---

## 3. React.memo for Toggling Components

Components that are frequently mounted / unmounted — such as **modals,
accordions, dialogs, dropdowns** — should be wrapped in `React.memo`:

```tsx
const ConfirmModal = React.memo(function ConfirmModal({
  open,
  onClose,
}: Props) {
  if (!open) return null;
  return <Dialog onClose={onClose}>…</Dialog>;
});
```

This prevents unnecessary re-renders when parent state changes but
the component's props haven't.

---

## 4. Export Pattern

- Use **function declarations** for components (not arrow-assigned):
  ```tsx
  export default function TaskList(): JSX.Element {
    /* … */
  }
  ```
- Use **default export** when the file contains a single isolated
  component.
- Use **named exports** when the file exports multiple components or
  utilities.

---

## 5. createPortal for Modals

Suggest `ReactDOM.createPortal` for modals to render them at the
document root, avoiding z-index and overflow clipping issues:

```tsx
import { createPortal } from "react-dom";

function Modal({ children }: { children: React.ReactNode }) {
  return createPortal(
    <div className='modal-overlay'>{children}</div>,
    document.body,
  );
}
```

---

## 6. Direct Imports from react

Import hooks and types directly — don't chain off `React.*`:

```tsx
// ✅ Preferred
import { useState, useEffect, JSX } from "react";

// ❌ Avoid
import React from "react";
React.useState();
```

---

## 7. Material UI + Tailwind

The project uses **Material UI** components styled with **Tailwind CSS**
utility classes. Combine them naturally:

```tsx
<Button variant='contained' className='mt-4 rounded-lg'>
  Save
</Button>
```

Don't fight the MUI theme system — use Tailwind for spacing, layout,
and small overrides only.

---

## 8. Repetitive Tag Sequences → .map()

Replace repetitive JSX tag sequences with `.map()`:

```tsx
// ❌ Repetitive
<MenuItem value="open">Open</MenuItem>
<MenuItem value="closed">Closed</MenuItem>
<MenuItem value="pending">Pending</MenuItem>

// ✅ Mapped
{["open", "closed", "pending"].map(s => (
  <MenuItem key={s} value={s}>{s.charAt(0).toUpperCase() + s.slice(1)}</MenuItem>
))}
```

---

## 9. React Query — invalidateQueries

`invalidateQueries` requires a dictionary with a `queryKey` array:

```tsx
queryClient.invalidateQueries({ queryKey: ["tasks"] });
```

**Not** a plain string or array argument.

---

## 10. ErrorBoundary

Suggest wrapping route-level or feature-level component trees in an
`ErrorBoundary` to catch rendering errors gracefully:

```tsx
<ErrorBoundary fallback={<ErrorPage />}>
  <TaskDashboard />
</ErrorBoundary>
```

Use the `react-error-boundary` package or a custom class component.

---

## 11. Next.js Rules

- Always use the **App Router** (`app/` directory).
- Separate `layout.tsx` from `page.tsx` — layouts shouldn't contain
  page-specific logic.
- Use `"use client"` only when the component genuinely needs browser
  APIs or interactivity.
- Prefer Server Components by default.
