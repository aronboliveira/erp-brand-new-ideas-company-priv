@extends('vendor.installer.layouts.master')

@php
    try {
$items = $permissions['permissions'] ?? [];
        $items = $items instanceof Collection ? $items : collect($items);

        $hasErrors = isset($permissions['errors']);
        $nextUrl   = Route::has('LaravelInstaller::environment') ? route('LaravelInstaller::environment') : '#';
        $canGoNext = !$hasErrors && $nextUrl !== '#';
    } catch (\Throwable $e) {
        \Log::error('vendor/installer/permissions — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@section('template_title')
    {{ trans('installer_messages.permissions.templateTitle') }}
@endsection

@section('title')
    <i class="fa fa-key fa-fw" aria-hidden="true"></i>
    {{ trans('installer_messages.permissions.title') }}
@endsection

@section('container')
    <ul class="list" role="list">
        @forelse($items as $perm)
            @php
                try {
                    $ok        = (bool) data_get($perm, 'isSet', false);
                    $folder    = (string) data_get($perm, 'folder', '');
                    $mode      = (string) data_get($perm, 'permission', '');
                    $statusCls = $ok ? 'success' : 'error';
                    $icon      = $ok ? 'check-circle-o' : 'exclamation-circle';
                    $statusTxt = $ok ? __('OK') : __('Missing');
                } catch (\Throwable $e) {
                    \Log::error('vendor/installer/permissions — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <li class="list__item list__item--permissions {{ $statusCls }}">
                <span aria-label="{{ __('Folder') }}: {{ $folder }}">{{ e($folder) }}</span>
                <span aria-label="{{ __('Permission') }}: {{ $mode }} ({{ $statusTxt }})">
                    <i class="fa fa-fw fa-{{ $icon }}" aria-hidden="true"></i>
                    {{ e($mode) }}
                </span>
            </li>
        @empty
            <li class="list__item list__item--permissions">
                {{ __('No permission entries found.') }}
            </li>
        @endforelse
    </ul>

    @if(!$hasErrors)
        <div class="buttons">
            <a href="{{ $nextUrl }}"
               class="button{{ $canGoNext ? '' : ' disabled' }}"
               @unless($canGoNext) aria-disabled="true" @endunless>
                {{ trans('installer_messages.permissions.next') }}
                <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
            </a>
        </div>
    @endif
@endsection
