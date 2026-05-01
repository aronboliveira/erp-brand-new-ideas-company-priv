@extends('layouts.admin')
@section('page-title')
    {{ __('Webhook Settings') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Webhook Settings') }}</li>
@endsection
@php
    $lang = 'en';
    $createBase = VW::WBH . '.create';
    $createKebab = Str::kebab($createBase);
    $createName = null;
    $createUrl = '#';
    $createGuard = '';
    $destroyBase = VW::WBH . '.destroy';
    $destroyKebab = Str::kebab($destroyBase);
    $destroyName = null;
    $editBase = VW::WBH . '.edit';
    $editKebab = Str::kebab($editBase);
    $editName = null;
    try {
        $lang = Utility::fetchUserLang() ?? 'en';
        $createName = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
        $createUrl = $createName ? route($createName) : '#';
        $createGuard = Utility::fetchLinkMessage($lang, VW::WBH, 'create_webhook_route_unavailable')
            ?? 'Create webhook route is unavailable.';
        $destroyName = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
        $editName = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
    } catch (\Throwable $e) {
        \Log::error('webhooks/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@section('action-btn')
    <div class="float-end">
        @can('create webhook')
            <a href="{{ $createUrl }}" class="{{ VC::BT_SM_PM }}" data-ajax-popup="true" data-title="{{ __('Create New Webhook') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}" data-guard-msg="{{ base64_encode($createGuard) }}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="{{ VC::C12 }}">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Module') }}</th>
                                    <th>{{ __('URL') }}</th>
                                    <th>{{ __('Method') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($webhookSettings ?? collect()) as $wh)
                                    <tr>
                                        <td>{{ $wh->module ?? '-' }}</td>
                                        <td>{{ $wh->url ?? '-' }}</td>
                                        <td><span class="badge bg-primary">{{ strtoupper($wh->method ?? 'GET') }}</span></td>
                                        <td class="Action">
                                            <span>
                                                @can('edit webhook')
                                                    @php
                                                        $editUrl = ($editName && ($wh->id ?? false))
                                                            ? route($editName, [$wh->id])
                                                            : '#';
                                                        $editGuard = Utility::fetchLinkMessage($lang ?? 'en', VW::WBH, 'edit_webhook_route_unavailable')
                                                            ?? 'Edit webhook route is unavailable.';
                                                    @endphp
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="{{ $editUrl }}" class="{{ VC::MX3 }} {{ VC::BT_SM_PM }}" data-ajax-popup="true" data-title="{{ __('Edit Webhook') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-guard-msg="{{ base64_encode($editGuard) }}">
                                                            <i class="ti ti-pencil {{ VC::TXT_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete webhook')
                                                    @php
                                                        $deleteUrl = ($destroyName && ($wh->id ?? false))
                                                            ? route($destroyName, [$wh->id])
                                                            : '#';
                                                        $deleteGuard = Utility::fetchLinkMessage($lang ?? 'en', VW::WBH, 'delete_webhook_route_unavailable')
                                                            ?? 'Delete webhook route is unavailable.';
                                                    @endphp
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'url' => $deleteUrl, 'id' => 'delete-form-' . ($wh->id ?? ''), 'data-guard-msg' => $deleteGuard]) !!}
<<<<<<< HEAD
                                                        <a href="#" class="{{ VC::MX3 }} {{ VC::BT_SM_PM }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-confirm-submit="{{ __('Are you sure?') }}">
=======
                                                        <a href="#" class="{{ VC::MX3 }} {{ VC::BT_SM_PM }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}" onclick="event.preventDefault(); if(confirm('{{ __('Are you sure?') }}')) this.closest('form').submit();">
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
                                                            <i class="ti ti-trash {{ VC::TXT_WT }}"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <div class="text-center p-4">
                                                <i class="fas fa-webhook" style="font-size: 48px; color: #ccc;"></i>
                                                <p class="mt-2">{{ __('No webhook settings found.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
