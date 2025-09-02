@extends('vendor.installer.layouts.master-update')
@section('title', trans('installer_messages.updater.welcome.title') ?: __('Could not find updater welcome title'))
@section('container')
    <p class="paragraph text-center">{{ trans('installer_messages.updater.welcome.message') ?: __('No welcome message available') }}</p>
    <div class="buttons">
        <a href="{{ route('LaravelUpdater::overview') }}" class="button">{{ trans('installer_messages.next') ?: __('Next') }}</a>
    </div>
@stop
