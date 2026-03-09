# Template Literal Testing Strategy

> Last updated: 2026-03-11

## Overview

The ERP Blade views use Laravel's `{{ }}` / `{!! !!}` syntax for server-side rendering. During TypeScript testing, we use **JavaScript template literals** (`${}`) to simulate these server-rendered values. At production time, the original Blade syntax must be preserved.

## The Problem

Blade views inject dynamic values into `<script>` blocks:

```blade
<script>
  var route_url = "{{ route('invoices.show', $invoice->id) }}";
  var csrf_token = "{{ csrf_token() }}";
  var app_url = "{!! config('app.url') !!}";
</script>
```

These cannot run in a Node.js test environment (no PHP interpreter). Test harness HTML pages need equivalent values in plain JS.

## Solution: Two Syntax Layers

### Layer 1: Tests (JS Template Literals)

In **harness HTML pages** and **test fixtures**, use JS template literals with mock values:

```html
<!-- tests/harness/pages/invoices-show.html -->
<script>
  var route_url = `/invoices/${window.__TEST_INVOICE_ID__ || 1}`;
  var csrf_token = "test-csrf-token-mock";
  var app_url = "http://localhost:8000";
</script>
```

### Layer 2: Production (Blade Syntax)

In **actual Blade views**, keep the original PHP syntax unchanged:

```blade
<!-- resources/views/invoices/show.blade.php -->
<script>
  var route_url = "{{ route('invoices.show', $invoice->id) }}";
  var csrf_token = "{{ csrf_token() }}";
  var app_url = "{!! config('app.url') !!}";
</script>
```

## Mapping Table

| Blade Expression | Test Equivalent | Notes |
|-----------------|----------------|-------|
| `{{ $variable }}` | `window.__TEST_VAR__` or literal | Escaped output |
| `{!! $variable !!}` | Same as above | Unescaped (HTML safe in tests) |
| `{{ route('name', $id) }}` | `` `/path/${mockId}` `` | Mock route URL |
| `{{ csrf_token() }}` | `"test-csrf-token-mock"` | Fixed test token |
| `{{ config('app.url') }}` | `"http://localhost:8000"` | Test server URL |
| `{{ auth()->id() }}` | `1` | Mock user ID |
| `{{ $settings['key'] }}` | `window.__TEST_SETTINGS__?.key` | Mock settings |
| `@json($data)` | JSON inline or `JSON.parse(...)` | Serialised data |

## Rules

1. **Never modify Blade files** to use JS template literals — they must remain valid Blade.
2. **Harness HTML files** (`tests/harness/pages/*.html`) use JS equivalents only.
3. **Test helpers** should provide mock globals via `window.__TEST_*` properties.
4. **Generator scripts** (`generate-harness.cjs`) must emit JS template syntax, never Blade.
5. When writing new Playwright or Jest tests, use the mock values from the harness.

## Mock Global Variables

These are set in the harness entry-point before route scripts load:

```html
<script>
  // Mock globals that Blade normally provides
  window.__TEST_CSRF__ = "test-csrf-token-mock";
  window.__TEST_APP_URL__ = "http://localhost:8000";
  window.__TEST_USER_ID__ = 1;
  window.__TEST_LOCALE__ = "en";
  window.__TEST_SETTINGS__ = { company_name: "Test Co" };
  window.translations = {};
</script>
```

## Validation

The CI pipeline does **not** run PHP — it only runs Node-based tests. Therefore:
- Blade syntax is validated by `php artisan view:cache` (server-side CI).
- Template literal syntax is validated by Playwright + Jest (frontend CI).
- No single test mixes both syntaxes.
