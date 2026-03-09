{{-- Minimal Inertia-compatible layout for Fortify/Jetstream routes.
     This ERP uses Blade for auth pages; this view exists only to prevent
     "View [app] not found" errors on residual Inertia routes. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ERP') }}</title>
</head>
<body>
    @inertia
    @if(!isset($page))
        <script>window.location.href = "{{ url('/dashboard') }}";</script>
    @endif
</body>
</html>
