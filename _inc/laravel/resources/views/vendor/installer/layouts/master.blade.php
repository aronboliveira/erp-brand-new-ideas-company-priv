@php
    use Illuminate\Support\Collection;
    $lang = App\Models\Utility::fetchUserLang();
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() ?? '' }}">
    <title>@hasSection('template_title')@yield('template_title') | @endif{{ trans('installer_messages.title') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('installer/img/favicon/favicon-16x16.png') }}" sizes="16x16"/>
    <link rel="icon" type="image/png" href="{{ asset('installer/img/favicon/favicon-32x32.png') }}" sizes="32x32"/>
    <link rel="icon" type="image/png" href="{{ asset('installer/img/favicon/favicon-96x96.png') }}" sizes="96x96"/>
    <link href="{{ asset('installer/css/style.min.css') }}" rel="stylesheet"/>
    @yield('style','')
    <script>
        window.Laravel = {!! json_encode(['csrfToken' => csrf_token() ?? ''], JSON_UNESCAPED_UNICODE) !!};
    </script>
</head>
<body>
<div class="master">
    <div class="box">
        <div class="header">
            <h1 class="header__title">@yield('title', __('No title available'))</h1>
        </div>
        <ul class="step">
            <li class="step__divider"></li>
            <li class="step__item {{ isActive('LaravelInstaller::final') }}">
                <i class="step__icon fa fa-server" aria-hidden="true"></i>
            </li>
            <li class="step__divider"></li>
            <li class="step__item {{ isActive('LaravelInstaller::environment')}} {{ isActive('LaravelInstaller::environmentWizard')}} {{ isActive('LaravelInstaller::environmentClassic')}}">
                @if(Request::is('install/environment') || Request::is('install/environment/wizard') || Request::is('install/environment/classic'))
                    <a href="{{ route('LaravelInstaller::environment') }}">
                        <i class="step__icon fa fa-cog" aria-hidden="true"></i>
                    </a>
                @else
                    <i class="step__icon fa fa-cog" aria-hidden="true"></i>
                @endif
            </li>
            <li class="step__divider"></li>
            <li class="step__item {{ isActive('LaravelInstaller::permissions') }}">
                @if(Request::is('install/permissions') || Request::is('install/environment') || Request::is('install/environment/wizard') || Request::is('install/environment/classic'))
                    <a href="{{ route('LaravelInstaller::permissions') }}">
                        <i class="step__icon fa fa-key" aria-hidden="true"></i>
                    </a>
                @else
                    <i class="step__icon fa fa-key" aria-hidden="true"></i>
                @endif
            </li>
            <li class="step__divider"></li>
            <li class="step__item {{ isActive('LaravelInstaller::requirements') }}">
                @if(Request::is('install') || Request::is('install/requirements') || Request::is('install/permissions') || Request::is('install/environment') || Request::is('install/environment/wizard') || Request::is('install/environment/classic'))
                    <a href="{{ route('LaravelInstaller::requirements') }}">
                        <i class="step__icon fa fa-list" aria-hidden="true"></i>
                    </a>
                @else
                    <i class="step__icon fa fa-list" aria-hidden="true"></i>
                @endif
            </li>
            <li class="step__divider"></li>
            <li class="step__item {{ isActive('LaravelInstaller::welcome') }}">
                @if(Request::is('install') || Request::is('install/requirements') || Request::is('install/permissions') || Request::is('install/environment') || Request::is('install/environment/wizard') || Request::is('install/environment/classic'))
                    <a href="{{ route('LaravelInstaller::welcome') }}">
                        <i class="step__icon fa fa-home" aria-hidden="true"></i>
                    </a>
                @else
                    <i class="step__icon fa fa-home" aria-hidden="true"></i>
                @endif
            </li>
            <li class="step__divider"></li>
        </ul>
        <div class="main">
            @if (session('message'))
                @php $msg = session('message'); @endphp
                <p class="alert text-center">
                    <strong>{{ !empty($msg) && is_array($msg) ? (data_get($msg,'message') ?: __('No message available')) : $msg }}</strong>
                </p>
                @php(Session::forget('message'))
            @endif
            @if ($message = Session::get('error_message'))
                <p class="alert text-center alert-danger">
                    <strong>{{ $message }}</strong>
                </p>
                @php(Session::forget('error_message'))
            @endif
            @if(session()->has('errors') && ($errors?->any() ?? false))
                <div class="alert alert-danger" id="error_alert">
                    <button type="button" class="close" id="close_alert" data-dismiss="alert" aria-hidden="true">
                        <i class="fa fa-close" aria-hidden="true"></i>
                    </button>
                    <h4>
                        <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                        {{ trans('installer_messages.forms.errorTitle') }}
                    </h4>
                    <ul>
                        @foreach(($errors?->all() ?? []) as $error)
                            <li>{{ $error ?: __('No error message available') }}</li>
                        @endforeach
                    </ul>
                </div>
                @php(Session::forget('errors'))
            @endif
            @yield('container', __('No content available'))
        </div>
    </div>
</div>
@yield('scripts','')
@if(session()->has('errors'))
    <script async src="{{ asset('assets/js/routes/installer/lang/dismiss.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/installer/dismiss.js') }}"></script>
@endif
</body>
</html>
