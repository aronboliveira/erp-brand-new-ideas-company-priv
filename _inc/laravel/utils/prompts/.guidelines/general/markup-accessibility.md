# Markup & Accessibility

Standards for HTML/JSX/Blade templates ensuring semantic correctness and accessibility compliance.

## Semantic Tags

- Always prefer semantic tag names (`<nav>`, `<article>`, `<section>`, `<aside>`, `<header>`, `<footer>`, `<main>`) over generic `<div>`.
- Self-closing tags must always be written as such (e.g., `<img />`, `<input />`, `<br />`).

## IDs

- All tags should have an `id` attribute. For those missing one, generate a list of suggested IDs and present for user approval.

## ARIA

- Add ARIA attributes to all tags where they would not be redundant, and always on essential interactive elements (buttons, links, form controls).
- Emojis must be wrapped in `<span role="img" aria-label="description">` with the label derived from the Unicode character name.

## Text Wrapping

- Avoid leaving text content without an enclosing tag. Even plain inline text should be wrapped in at least a `<span>`. Present cases to the user for confirmation.

## JSX Quoting

- Attributes commonly templated (e.g., `className`, `id`): assign using `={``${...}``}`.
- All other attributes: use single quotes unless double quotes are strictly necessary.
- JSX single-quote preference aligns with project `.prettierrc` (`jsxSingleQuote: true`).

## Repetitive Sequences

- Repetitive tag sequences → replace with `.map()` iterations in JSX/React.
- Suggest `ErrorBoundary` wrappers wherever appropriate.
