<title>{{ config('chatify.name') ?: __('No application name available') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="route" content="{{ (string) ($route ?? __('No route available')) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="{{ asset('css/chatify/style.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/chatify/'.(($dark_mode ?? 'light')).'.mode.css') }}" rel="stylesheet"/>
@include('Chatify::layouts.messenger_color')
