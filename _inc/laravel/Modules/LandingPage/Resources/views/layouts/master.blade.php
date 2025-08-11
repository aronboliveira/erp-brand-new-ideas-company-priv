@php
    use Modules\LandingPage\Config\Constants\{
        DatabaseConstants,
        YieldingConstants
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
    <head>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc
        ])
        <title>LandingPage</title>
        {{-- Laravel Vite - CSS File --}}
        {{-- {{ module_vite('build-landingpage', 'Resources/assets/sass/app.scss') }} --}}
    </head>
    <body>
        @yield(YieldingConstants::MST_CTT)
        {{-- Laravel Vite - JS File --}}
        {{-- {{ module_vite('build-landingpage', 'Resources/assets/js/app.js') }} --}}
        <script>
            console.log(
                'Current route:',
                '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
            );
        </script>
    </body>
</html>
