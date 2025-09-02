@extends('vendor.installer.layouts.master')

@php
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Route;

    $hasNext = Route::has('LaravelInstaller::requirements');
    $nextUrl = $hasNext ? route('LaravelInstaller::requirements') : '#';
@endphp

@section('template_title')
    {{ trans('installer_messages.welcome.templateTitle') }}
@endsection

@section('title')
    {{ trans('installer_messages.welcome.title') }}
@endsection

@section('container')
    <p class="text-center">
        {{ trans('installer_messages.welcome.message') }}
    </p>
    <p class="text-center">
        <a href="{{ $nextUrl }}" class="button" @unless($hasNext) aria-disabled="true" tabindex="-1" @endunless>
            {{ trans('installer_messages.welcome.next') }}
            <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
        </a>
    </p>
@endsection
