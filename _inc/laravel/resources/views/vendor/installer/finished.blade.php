@extends('vendor.installer.layouts.master')

@section('template_title')
    {{ trans('installer_messages.final.templateTitle') }}
@endsection

@section('title')
    <i class="fa fa-flag-checkered fa-fw" aria-hidden="true"></i>
    {{ trans('installer_messages.final.title') }}
@endsection

@section('container')
    @php
        try {
$showSeededCreds = (bool) config('installer.show_seeded_credentials', app()->environment('local'));

            $messageBag    = session('message', []);
            $dbOutputLog   = data_get($messageBag, 'dbOutputLog');
            $consoleOutput = $finalMessages       ?? '';
            $statusLog     = $finalStatusMessage  ?? '';
            $envDump       = $finalEnvFile        ?? '';
        } catch (\Throwable $e) {
            \Log::error('vendor/installer/finished — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp

    @if($showSeededCreds)
        <div role="alert" aria-live="polite" class="alert alert-warning" style="margin-bottom:1rem;">
            <strong>{{ __('Seeded Accounts (local only)') }}:</strong><br>
            {{ __('Super Admin') }}: <code>superadmin@example.com / 1234</code><br>
            {{ __('Company') }}: <code>company@example.com / 1234</code><br>
            {{ __('User') }}: <code>accountant@example.com / 1234</code>
        </div>
    @endif

    @if(!empty($dbOutputLog))
        <p><strong><small>{{ trans('installer_messages.final.migration') }}</small></strong></p>
        <pre><code>{{ $dbOutputLog }}</code></pre>
    @endif

    <p><strong><small>{{ trans('installer_messages.final.console') }}</small></strong></p>
    <pre><code>{{ $consoleOutput }}</code></pre>

    <p><strong><small>{{ trans('installer_messages.final.log') }}</small></strong></p>
    <pre><code>{{ $statusLog }}</code></pre>

    <p><strong><small>{{ trans('installer_messages.final.env') }}</small></strong></p>
    <pre><code>{{ $envDump }}</code></pre>

    <div class="buttons">
        <a href="{{ url('/') }}" class="button">{{ trans('installer_messages.final.exit') }}</a>
    </div>
@endsection
