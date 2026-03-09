# Route Conventions

## UUID-Only IDs

All main resource IDs are **UUIDs** — never integers. Route parameters must use
`where('id', '[0-9a-f\-]{36}')` or a UUID regex constraint.

## Path Sorting Order

Routes must be sorted by:

1. **Number of subpaths** — ascending (fewer segments first).
2. **Alphabetical** — ascending within the same depth.
3. **Static before dynamic** — at equal depth, static paths (`/users/export`) come before
   dynamic paths (`/users/{id}`).

### Example

```
GET  /companies
GET  /companies/export
GET  /companies/{id}
GET  /companies/{id}/departments
POST /companies/{id}/departments
GET  /companies/{id}/departments/{deptId}
```

## Preserve Comments

Existing route-file comments must remain as-is. Do not remove or rewrite them when
reordering routes.

## Route::has Guards

Before rendering a link or form that references a named route, check the route exists:

```php
@if (Route::has('invoices.show'))
    <a href="{{ route('invoices.show', $invoice->id) }}">View</a>
@endif
```

This prevents runtime exceptions when modules are disabled or routes are removed.

## Naming Convention

- Route names follow dot notation: `module.resource.action`
- Use **kebab-case** as fallback for multi-word segments: `project-tasks.index`

## Localized Link Messages

Use the `Utility::fetchLinkMessage()` helper for localized link labels:

```php
Utility::fetchLinkMessage($url, $key, $lang)
```

## Script Pushing

Use `@push` with the `StacksConstants::ADM_SCRP_PG` constant to append page-specific scripts:

```blade
@push(\App\Config\Constants\StacksConstants::ADM_SCRP_PG)
    <script src="{{ asset('js/page-specific.js') }}"></script>
@endpush
```

## Localization Markers

Add `data-sv-localized` attribute to elements whose text comes from the translation system,
enabling front-end tooling to identify translatable content.
