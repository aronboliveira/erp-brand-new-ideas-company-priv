# Blade Template Hardening

## Variable Initialization

Use `??=` in `@php` blocks at the top of every template to set safe defaults for every
variable the template references. This prevents undefined-variable errors even when the
controller omits a key:

```blade
@php
    $title    ??= '';
    $items    ??= [];
    $customer ??= null;
    $canEdit  ??= false;
@endphp
```

## Exception Handling

Wrap risky sections in `try/catch` blocks with **diverse exception types**. Catch the most
specific exceptions first, then `Exception`, then `Throwable`:

```blade
@php
try {
    $total = $invoice->computeTotal();
} catch (\InvalidArgumentException $e) {
    Log::warning($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    $total = 0;
} catch (\Exception $e) {
    Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    $total = 0;
} catch (\Throwable $e) {
    Log::critical($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    $total = 0;
}
@endphp
```

## Defensive Guards

Apply guards before accessing any variable or property:

- `isset($var)` — variable exists and is not null
- `empty($var)` — falsy check
- `is_array($var)` — confirm array before iteration
- `data_get($object, 'nested.key', $default)` — safe nested access

```blade
@if (isset($items) && is_array($items) && !empty($items))
    @foreach ($items as $item)
        {{ data_get($item, 'name', 'N/A') }}
    @endforeach
@endif
```

## Auth Caching

Cache `Auth::user()` in a local variable to avoid repeated queries:

```blade
@php
    $authUser = Auth::user();
@endphp

@if ($authUser && $authUser->can('edit-invoices'))
    ...
@endif
```

## Import Aliasing

Use aliases for constants classes to keep Blade readable:

```blade
@php
    use App\Config\Constants\InvoicesConstants as IC;
    use App\Config\Constants\StacksConstants as SC;
@endphp
```

## Form Patterns

Use `Form::open` / `Form::model` with route resolution:

```blade
@if (Route::has('invoices.update'))
    {!! Form::model($invoice, [
        'route'  => ['invoices.update', $invoice->id],
        'method' => 'PUT',
        'id'     => 'invoice-form',
    ]) !!}
        {!! Form::text('title', null, ['class' => 'form-control']) !!}
        {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    {!! Form::close() !!}
@endif
```

## JS IIFE for Form Submit Guarding

Prevent double-submit with an IIFE:

```blade
@push(\App\Config\Constants\StacksConstants::ADM_SCRP_PG)
<script>
(function () {
    'use strict';
    const form = document.getElementById('invoice-form');
    if (!form) return;
    let submitted = false;
    form.addEventListener('submit', function (e) {
        if (submitted) { e.preventDefault(); return; }
        submitted = true;
    });
})();
</script>
@endpush
```

## Script Pushing

Always use `@push` with the appropriate stack constant — never inline scripts outside of a
`@push` block in templates.
