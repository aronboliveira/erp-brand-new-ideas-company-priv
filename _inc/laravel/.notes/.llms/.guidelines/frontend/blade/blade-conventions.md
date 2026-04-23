# Convenções Blade / Blade Template Conventions

> Para subagentes trabalhando com templates em `resources/views/`.

## Estrutura

```
resources/views/
├── auth/          # Login, register, forgot password
├── bills/         # Invoices, bills, expenses
├── contracts/     # Contract views
├── customers/     # Customer views
├── expenses/      # Expense create/edit
├── layouts/       # admin.blade.php (main layout)
├── projects/      # Project views
├── webhooks/      # Webhook views (não "webhook" — corrigido)
└── ...
```

## Regras

### 1. Route Helpers

```blade
{{-- CORRETO — rota sem ID de projeto --}}
{{ route('expenses.index') }}

{{-- ERRADO — requer {pid} que pode não existir --}}
{{ route('projects.expenses.index') }}
```

Sempre usar `Route::has()` como guard antes de `route()`.

### 2. Form::open

```blade
{{-- URL absoluta: usar 'url', não 'route' --}}
{!! Form::open(['url' => $storeUrl, 'method' => 'POST']) !!}

{{-- Route name: usar 'route' --}}
{!! Form::open(['route' => 'expenses.store', 'method' => 'POST']) !!}
```

### 3. Collection Casting

```blade
{{-- CORRETO --}}
@foreach(Utility::languages()->all() as $lang)

{{-- ERRADO — cast de Collection retorna array com keys internos --}}
@foreach((array)(Utility::languages()) as $lang)
```

### 4. RTL Support

`resources/views/layouts/admin.blade.php` — `dir="rtl"` detectado do locale (ar, he).

### 5. View Constants

- `VW::INV` para invoices, `VW::BIL` para bills, etc.
- Cuidado com separador `.`: `VW::INV . '.link.copy'` (não `VW::INV . 'link.copy'`)

### 6. Comentários

Comentários em Blade em pt-BR:

```blade
{{-- Exibe o formulário de criação de despesa --}}
```
