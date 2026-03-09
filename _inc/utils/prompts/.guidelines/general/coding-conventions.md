# General Coding Conventions

Cross-language standards applied across the entire ERP Prestech codebase (PHP, JS/TS, Python).

## Comments Policy

Only include comments that point where logic should be added or refactored, with commented-out suggestions. Never add purely descriptive comments — the code itself should be self-documenting.

## Punctuation

- Always include trailing semicolons in languages that support them.
- Never insert blank lines purely for readability — every newline must serve a structural purpose.

## Spacing

- **Print width**: 80 characters.
- **Tab width**: 2 spaces (JS/TS default — PHP override to 4 in `.editorconfig`).
- Trailing whitespace must be removed.
- Never use more than one whitespace to separate arguments on the same line.
- Maintain an indentation pattern that enables clean code folding for blocks, statements, dictionary keys, and array elements.
- Throughout sessions, suggest user snippet templates when repetitive generation patterns emerge (provide a ready-to-save JSON definition).

## Statements

- Pairs of `if`/`else` blocks leading to single statements in both branches → replace with ternary operators.
- Never hardcode namespace, class, function, or method names as strings; use dynamic reflection methods instead (e.g., `__CLASS__`, `__FUNCTION__`, `inspect.currentframe()` in Python).
- Replace repetitive sequential lines (e.g., many attribute assignments or string constructions with visible patterns) with iterative procedures.

## Imports & Exports

- Sort alphabetically at all depths.

## Static Methods

- Only use `static` when there is genuinely no need for class-level or instance-level references.

## Route Lists

- Do not add comments inside route definition lists.

## Classes

- Never convert a class into a standalone function even if it has only one method without instance properties. Instead, make the single method `static` (PHP) or a `@classmethod`/`@staticmethod` (Python).
