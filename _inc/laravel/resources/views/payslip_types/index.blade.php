@php
    try {
$user = Auth::user();
        $fetchLinkAv = is_callable([Utility::class,'fetchLinkMessage']);
        $lang = $fetchLinkAv ? Utility::fetchUserLang(user:$user) : app()->getLocale();

        $dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
        $dashGuard = ($fetchLinkAv ? Utility::fetchLinkMessage($lang,'generics','dashboard_unavailable') : null) ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

        $items = [];
        if (is_array($paysliptypes ?? null) && count($paysliptypes)) $items = $paysliptypes;
        elseif (($paysliptypes ?? null) instanceof Collection && $paysliptypes->isNotEmpty()) $items = $paysliptypes;
    } catch (\Throwable $e) {
        \Log::error('payslip_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Payslip Type') }}
@endsection

@section(YD::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ base64_encode($dashGuard) }}"
           {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Payslip Type') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create payslip type')
            @php
                $createUrl   = Route::has(VW::PY_SLP_TP.'.create') ? route(VW::PY_SLP_TP.'.create') : '#';
                $createGuard = ($fetchLinkAv ? Utility::fetchLinkMessage($lang, VW::PY_SLP_TP, 'create_payslip_type_unavailable') : 'Create Payslip Type route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Payslip Type route is unavailable. Please contact technical support or your domain administrator.');
@endphp
            <a href="{{ $createUrl }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-title="{{ __('Create New Payslip Type') }}"
               data-sv-localized="true"
               data-guard-msg="{{ base64_encode($createGuard) }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YD::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C3 }}">
            @include('layouts.hrm_setup')
        </div>
        <div class="{{ VC::C9 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                            <tr>
                                <th>{{ __('Payslip Type') }}</th>
                                @canany(['edit payslip type','delete payslip type'])
                                    <th width="200px">{{ __('Action') }}</th>
                                @endcanany
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @forelse($items as $paysliptype)
                                @php
                                    $title = isset($paysliptype->name) && $paysliptype->name !== '' ? $paysliptype->name : __('No title available');
@endphp
                                <tr>
                                    <td>{{ $title }}</td>
                                    @canany(['edit payslip type','delete payslip type'])
                                        <td>
                                            @can('edit payslip type')
                                                @php
                                                    $editUrl   = Route::has(VW::PY_SLP_TP.'.edit') ? route(VW::PY_SLP_TP.'.edit',$paysliptype->id) : '#';
                                                    $editGuard = ($fetchLinkAv ? Utility::fetchLinkMessage($lang, VW::PY_SLP_TP, 'edit_payslip_type_unavailable') : 'Edit Payslip Type route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Payslip Type route is unavailable. Please contact technical support or your domain administrator.');
@endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a href="{{ $editUrl }}"
                                                       class="{{ VC::BT_SM_CT }}"
                                                       data-url="{{ $editUrl }}"
                                                       data-ajax-popup="true"
                                                       data-title="{{ __('Edit Payslip Type') }}"
                                                       data-sv-localized="true"
                                                       data-guard-msg="{{ base64_encode($editGuard) }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Edit') }}">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete payslip type')
                                                @php
                                                    $delUrl   = Route::has(VW::PY_SLP_TP.'.destroy') ? route(VW::PY_SLP_TP.'.destroy',$paysliptype->id) : '#';
                                                    $delGuard = ($fetchLinkAv ? Utility::fetchLinkMessage($lang, VW::PY_SLP_TP, 'delete_payslip_type_unavailable') : 'Delete Payslip Type route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete Payslip Type route is unavailable. Please contact technical support or your domain administrator.');
@endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open(['method' => 'DELETE', 'url' => $delUrl, 'id'=>'delete-form-'.$paysliptype->id, 'data-url'=>$delUrl, 'data-sv-localized'=>'true', 'data-guard-msg'=>$delGuard]) !!}
                                                        <a href="{{ $delUrl }}"
                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                           data-url="{{ $delUrl }}"
                                                           data-sv-localized="true"
                                                           data-guard-msg="{{ base64_encode($delGuard) }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __($fetchLinkAv ? (Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') : __('Are you sure?')) }}|{{ __($fetchLinkAv ? (Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') : __('This action can not be undone. Do you want to continue?')) }}"
                                                           data-confirm-yes="document.getElementById('delete-form-{{$paysliptype->id}}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    @endcanany
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="{{ VC::TXCT_MT }}">{{ __('No payslip types found.') }}</td>
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

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/payslipTypes/index.js') }}"></script>
@endpush
