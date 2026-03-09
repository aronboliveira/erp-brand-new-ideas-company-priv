@extends('vendor.installer.layouts.master-update')
@section('title', trans('installer_messages.updater.welcome.title') ?: __('Could not find title'))
@section('container')
    @php
 $pending = is_numeric($numberOfUpdatesPending ?? null) ? (int) $numberOfUpdatesPending : 0;
@endphp
    <p class="paragraph text-center">{{ trans_choice('installer_messages.updater.overview.message', $pending, ['number' => $pending]) ?: __('No update information available') }}</p>
    <div class="buttons">
        <a href="{{ route('LaravelUpdater::database') }}" class="button">{{ trans('installer_messages.updater.overview.install_updates') ?: __('Install updates') }}</a>
    </div>
@stop
