@php
@endphp
@if (($colorSettings[SettingsConstants::CST_DRK] ?? null) === 'on'
	&& is_file(asset('assets/css/style-dark.css')))
	<link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}" id="main-style-link">
@else
	<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
@endif
<link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
<link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
<link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
<link rel="stylesheet" href="{{ asset('css/custom.css') }}" id="custom-style-link">
<link
  href="https://cdn.jsdelivr.net/npm/bootstrap@5.x/dist/css/bootstrap.min.css"
  rel="stylesheet">
@if (($colorSettings[SettingsConstants::CST_DRK] ?? null) === 'on' && is_file(asset('css/custom-dark.css')))
	<link rel="stylesheet" href="{{ asset('css/custom-dark.css') }}" id="custom-dark-style-link">
@endif
