# CSS & SCSS Styling

Guidelines for styling across the ERP Brand New Ideas Company codebase using CSS, SCSS, Material UI, and Tailwind CSS.

## Pseudo-Classes

Apply styling rules to these pseudo-classes when valuable for UI/UX:
`:active`, `:hover`, `:checked`, `:disabled`, `:enabled`, `:invalid`, `:valid`, `:empty`, `:(nth)-child`, `:(nth)-of-type`, `:required`

**Mandatory**: Always style `:active` and `:hover` for highlighted tags, buttons (and inputs/divs that mimic buttons), and links.

## Pseudo-Elements

Apply when valuable: `::selection`, `::placeholder`, `::marker`, `::details-content`, `::first-letter`, `::first-line`.

## Modern SCSS Patterns

- Box shadows, text drop shadows, subtle gradient colors, cropped images, border-radius, and reactive CSS based on pseudo-classes/pseudo-elements.
- Broad usage of SCSS nesting.
- Use SCSS variables when values repeat. Use SCSS mixins when blocks of styling repeat.

## Selector & Rule Ordering

- Sort selectors alphabetically.
- Sort rules by functional groups (spacing, fonts, borders, etc.).

## Library Priority

Material UI takes priority over Tailwind CSS when used with React. Tailwind supplements MUI rather than replaces it.

## Visual Identity

Match the attached HTML and/or CSS visual identity when reference files are present.
