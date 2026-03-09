@extends('vendor.installer.layouts.master-update')
@section('title', trans('installer_messages.updater.final.title'))
@section('container')
    @php
        $msg = session('message');
        $finalMsg = !empty($msg) && is_array($msg) ? (data_get($msg,'message') ?? __('No update message available')) : ($msg ?? __('No update message available'));
@endphp
    <p class="paragraph text-center">{{ $finalMsg }}</p>
    <div class="buttons">
        <a href="{{ url('/') }}" class="button">{{ trans('installer_messages.updater.final.exit') }}</a>
    </div>
@stop
