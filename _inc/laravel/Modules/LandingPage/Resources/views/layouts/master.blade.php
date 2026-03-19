@php
    try {

    } catch (\Throwable $e) {
        \Log::error('Modules/LandingPage/Resources/views/layouts/master — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
    <head>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc
        ])
        <title>{{ __('LandingPage') }}</title>
        {{-- Laravel Vite - CSS File --}}
        {{-- {{ module_vite('build-landingpage', 'Resources/assets/sass/app.scss') }} --}}
    </head>
    <body>
        @yield(YieldingConstants::MST_CTT)
        {{-- Laravel Vite - JS File --}}
        {{-- {{ module_vite('build-landingpage', 'Resources/assets/js/app.js') }} --}}
        <script>
            (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') && console.log(
                'Current route:',
                '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
            );
        </script>
    </body>
</html>
