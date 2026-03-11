# TypeScript Type-Safety Rules

## Overview

These rules govern TypeScript-specific patterns in the ERP Prestech
codebase. They complement the general JavaScript coding rules and focus
on type declarations, variable typing, and framework integration.

---

## 1. Separate Type Declaration Modules

Write `interface` and `type` declarations in a dedicated module file
(e.g., `types.ts`, `models.d.ts`), not inline alongside implementation.
This keeps implementation files clean and makes types reusable.

```ts
// types/task.ts
export interface Task {
  id: number;
  title: string;
  status: "open" | "closed";
}
```

---

## 2. SelectChangeEvent for MUI Select

When handling Material UI `<Select>` change events, use the
`SelectChangeEvent` type from `@mui/material`:

```ts
import { SelectChangeEvent } from "@mui/material";

function handleChange(e: SelectChangeEvent): void {
  const value = e.target.value;
}
```

Never use `React.ChangeEvent<HTMLSelectElement>` for MUI Select — it
doesn't match the actual event shape.

---

## 3. Never Leave `never` or `unknown` Types

If a variable is inferred as `never`, the logic is unreachable or a type
narrowing error exists. **Flag it immediately**:

```ts
// ! NEVER — unreachable: check union narrowing above
```

If a variable is typed as `unknown`, narrow it before use or cast it
explicitly. **Flag it**:

```ts
// ! UNKNOWN — needs explicit type or runtime check
```

Never ship code with unflagged `never` or `unknown` — they indicate
bugs or missing type information.

---

## 4. Explicit Iterable Types

Iterables (arrays, Sets, Maps) must have their element type explicitly
declared. For nested arrays, use alternating generic syntax to keep
nesting unambiguous:

```ts
// ✅ Clear nesting
const grid: Array<Array<string[]>[]> = [];

// ❌ Ambiguous
const grid: string[][][] = [];
```

---

## 5. Prefer Interfaces Over Type Aliases

For object shapes, prefer `interface` over `type`:

```ts
// ✅ Preferred
interface User {
  id: number;
  name: string;
}

// ❌ Avoid for object shapes
type User = {
  id: number;
  name: string;
};
```

Reserve `type` aliases for unions, intersections, mapped types, and
utility types.

---

## 6. JSX Import

When declaring JSX return types (`JSX.Element`, `React.ReactNode`),
always import `JSX` from `react`:

```ts
import { JSX } from "react";

function Widget(): JSX.Element {
  return <div />;
}
```

Do not rely on the global JSX namespace — it is deprecated in React 19+.

---

## 7. Async Return Types

| Signature          | Annotation                                    |
| ------------------ | --------------------------------------------- |
| `async function`   | `Promise<void>`                               |
| Non-async function | `: void`                                      |
| Complex inferred   | Add `// eslint-disable-next-line` with reason |

```ts
async function loadData(): Promise<void> {
  /* … */
}
function handleClick(): void {
  /* … */
}
```

---

## 8. Complex Inferred Returns

When the inferred return type is too complex or noisy to annotate
manually, suppress the ESLint rule on that line with a reason:

```ts
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type -- generated union too complex
const buildConfig = () => ({
  /* … */
});
```

Prefer annotating whenever practical. Suppression is a last resort.
