@extends('vendor.installer.layouts.master')

@section('template_title')
    {{ trans('installer_messages.environment.menu.templateTitle') }}
@endsection

@section('title')
    <i class="fa fa-cog fa-fw" aria-hidden="true"></i>
    {!! trans('installer_messages.environment.menu.title') !!}
@endsection

@section('container')
    @php
        use Illuminate\Support\Facades\Route;

        $wizardRoute  = Route::has('LaravelInstaller::environmentWizard')  ? route('LaravelInstaller::environmentWizard')  : '#';
        $classicRoute = Route::has('LaravelInstaller::environmentClassic') ? route('LaravelInstaller::environmentClassic') : '#';

        $wizardDisabled  = $wizardRoute  === '#';
        $classicDisabled = $classicRoute === '#';
    @endphp

    <p class="text-center">
        {!! trans('installer_messages.environment.menu.desc') !!}
    </p>

    <div class="buttons">
        <a href="{{ $wizardRoute }}"
           class="button button-wizard{{ $wizardDisabled ? ' disabled' : '' }}"
           @if($wizardDisabled) aria-disabled="true" @endif>
            <i class="fa fa-sliders fa-fw" aria-hidden="true"></i>
            {{ trans('installer_messages.environment.menu.wizard-button') }}
        </a>

        <a href="{{ $classicRoute }}"
           class="button button-classic{{ $classicDisabled ? ' disabled' : '' }}"
           @if($classicDisabled) aria-disabled="true" @endif>
            <i class="fa fa-code fa-fw" aria-hidden="true"></i>
            {{ trans('installer_messages.environment.menu.classic-button') }}
        </a>
    </div>
@endsection
