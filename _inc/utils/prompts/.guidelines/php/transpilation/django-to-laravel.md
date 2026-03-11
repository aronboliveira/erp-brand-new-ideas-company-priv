# Django → Laravel Transpilation Guide

## General Conventions

- **camelCase** for all PHP identifiers **except**:
  - Class names → `PascalCase`
  - Database fields → `snake_case` (matching migration column names)
- Follow PSR-12 coding standards throughout.

## Helper Functions

The transpilation process relies on a set of shared helper functions that replace
Django equivalents:

### `getRedirectUrl(string $routeName, array $params = []): string`

Returns the URL for a named route with optional parameters. Replaces Django's `reverse()`.

### `defaultPermissionDenial(): Response`

Returns a standardized 403 response. Replaces Django's `PermissionDenied` exception.

### `defaultUndefinedException(string $message = ''): Response`

Returns a standardized 500 error response for unexpected states.

### `emailValidation(string $email): bool`

Validates email format. Replaces Django's `EmailValidator`.

### `userIdValidation(string $id): bool`

Validates that a UUID string is well-formed and the user exists.

### `permissionRequiredCustom(string $permission): \Closure`

Returns middleware closure for custom permission checks. Replaces Django's
`@permission_required` decorator.

## Traits

### `ChecksLogin`

Replaces Django's `LoginRequiredMixin`. Applied to controllers that require authentication.

### `ChecksPermissions`

Replaces Django's `PermissionRequiredMixin`. Provides `authorizePermission()` method.

## Layer-Specific Rules

### Controllers

- **Create new endpoints** for each Django view method that lacks its own dedicated route.
  One Django view = one Laravel controller method = one route.
- Repetitive endpoint arguments (e.g., module prefix, middleware group) → store as
  `private const` on the controller.

### Middleware

- **Extensive logging** for all activities — log request entry, permission checks, and outcomes.
- Defensive programming: validate all inputs, check all preconditions.
- Follow DRY, ACID, and RESTful principles.

### Models

- **Never change querying logic** from the original Django model. If a query needs modification,
  add a comment with the suggestion instead.
- Mark all field name changes with `// ! CHANGED` inline comment.
- UUID primary keys with `UsesUuids` trait (replaces Django's `UUIDField(primary_key=True)`).

### Exports

Two approaches are available:

1. **Laravel-centered**: Use Laravel Excel (`Maatwebsite\Excel`) for spreadsheet generation,
   styling, and reading.
2. **Python-endpoint**: Keep complex transformations in a Python microservice, call via HTTP.

Spreadsheet conventions:

- Apply consistent styling (headers bold, borders, column widths).
- Use Excel functions for calculated cells where possible.
- Read operations use chunk reading for memory efficiency.

## Cross-Reference

General coding guidelines apply from `general/` directory (cross-language rules).
JavaScript-specific rules apply from `javascript/` directory for any JS in the transpiled code.
