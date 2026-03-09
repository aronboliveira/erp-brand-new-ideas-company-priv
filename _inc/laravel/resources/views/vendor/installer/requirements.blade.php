@extends('vendor.installer.layouts.master')

@php
    try {
$requirementsArr = $requirements['requirements'] ?? [];
            $requirementsByType = $requirementsArr instanceof Collection ? $requirementsArr : collect($requirementsArr);

        $phpInfo       = $phpSupportInfo ?? [];
        $phpSupported  = (bool) data_get($phpInfo, 'supported', false);
        $phpCurrent    = (string) data_get($phpInfo, 'current', '');
        $phpMinimum    = (string) data_get($phpInfo, 'minimum', '');

        $hasErrors     = (bool) data_get($requirements, 'errors', false);

        $nextUrl       = Route::has('LaravelInstaller::permissions') ? route('LaravelInstaller::permissions') : '#';
        $canProceed    = $phpSupported && !$hasErrors && $nextUrl !== '#';
    } catch (\Throwable $e) {
        \Log::error('vendor/installer/requirements — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@section('template_title')
    {{ trans('installer_messages.requirements.templateTitle') }}
@endsection

@section('title')
    <i class="fa fa-list-ul fa-fw" aria-hidden="true"></i>
    {{ trans('installer_messages.requirements.title') }}
@endsection

@section('container')
    @forelse ($requirementsByType as $type => $group)
        @php
                        try {
                            $headerStatusClass = $type === 'php'
                                            ? ($phpSupported ? 'success' : 'error')
                                            : '';
                                        $group = $group instanceof Collection ? $group->toArray() : (array) $group;
                        } catch (\Throwable $e) {
                            \Log::error('vendor/installer/requirements — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp

        <ul class="list" role="list">
            <li class="list__item list__title {{ $headerStatusClass }}">
                <strong>{{ ucfirst((string) $type) }}</strong>

                @if ($type === 'php')
                    <strong class="ms-1">
                        <small>(version {{ $phpMinimum }} {{ __('required') }})</small>
                    </strong>
                    <span class="float-right">
                        <strong>{{ $phpCurrent }}</strong>
                        <i class="fa fa-fw fa-{{ $phpSupported ? 'check-circle-o' : 'exclamation-circle' }} row-icon" aria-hidden="true"></i>
                    </span>
                @endif
            </li>

            @foreach ($group as $extension => $enabled)
                @php
                    try {
                        $ok   = (bool) $enabled;
                        $icon = $ok ? 'check-circle-o' : 'exclamation-circle';
                        $cls  = $ok ? 'success' : 'error';
                    } catch (\Throwable $e) {
                        \Log::error('vendor/installer/requirements — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <li class="list__item {{ $cls }}">
                    {{ (string) $extension }}
                    <i class="fa fa-fw fa-{{ $icon }} row-icon" aria-hidden="true"></i>
                </li>
            @endforeach
        </ul>
    @empty
        <ul class="list">
            <li class="list__item">
                {{ __('No requirement groups found.') }}
            </li>
        </ul>
    @endforelse

    @if ($canProceed)
        <div class="buttons">
            <a class="button" href="{{ $nextUrl }}">
                {{ trans('installer_messages.requirements.next') }}
                <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
            </a>
        </div>
    @endif
@endsection
