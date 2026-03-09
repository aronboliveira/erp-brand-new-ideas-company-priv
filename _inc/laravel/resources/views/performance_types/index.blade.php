@php
    try {
$user = Auth::user();
        $lang = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
        $canFetchMsg = is_callable([Utility::class, 'fetchLinkMessage']);

        $dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
        $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : null) ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

        $items = [];
        if (is_array($types ?? null) && count($types)) {
            $items = $types;
        } elseif (($types ?? null) instanceof Collection && $types->isNotEmpty()) {
            $items = $types;
        }
    } catch (\Throwable $e) {
        \Log::error('performance_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Performance type') }}
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
    <li class="{{ VC::BCI_ACT }}" aria-current="page">{{ __('Performance Type') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create performance type')
            @php
                $createUrl   = Route::has(VW::PFM_TP.'.create') ? route(VW::PFM_TP.'.create') : '#';
                $createGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PFM_TP, 'create_performance_type_unavailable') : 'Create Performance Type route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Performance Type route is unavailable. Please contact technical support or your domain administrator.');
@endphp
            <a href="{{ $createUrl }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-title="{{ __('Create New Performance Type') }}"
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
                                    <th scope="col">{{ __('Name') }}</th>
                                    @canany(['edit performance type','delete performance type'])
                                        <th scope="col">{{ __('Action') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody class="list">
                                @forelse($items as $type)
                                    @php
                                        $name = isset($type->name) && $type->name !== '' ? $type->name : __('No name for performance type available');
@endphp
                                    <tr class="font-style">
                                        <td>{{ $name }}</td>
                                        @canany(['edit performance type','delete performance type'])
                                            <td>
                                                @can('edit performance type')
                                                    @php
                                                        $editUrl   = Route::has(VW::PFM_TP.'.edit') ? route(VW::PFM_TP.'.edit', $type->id) : '#';
                                                        $editGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PFM_TP, 'edit_performance_type_unavailable') : 'Edit Performance Type route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Performance Type route is unavailable. Please contact technical support or your domain administrator.');
@endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a href="{{ $editUrl }}"
                                                           class="{{ VC::BT_SM_CT }}"
                                                           data-url="{{ $editUrl }}"
                                                           data-ajax-popup="true"
                                                           data-title="{{ __('Edit Performance Type') }}"
                                                           data-sv-localized="true"
                                                           data-guard-msg="{{ base64_encode($editGuard) }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete performance type')
                                                    @php
                                                        try {
                                                            $delUrl   = Route::has(VW::PFM_TP.'.destroy') ? route(VW::PFM_TP.'.destroy', $type->id) : '#';
                                                            $delGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PFM_TP, 'delete_performance_type_unavailable') : 'Delete Performance Type route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete Performance Type route is unavailable. Please contact technical support or your domain administrator.');
                                                            $formId   = 'delete-form-'.$type->id;
                                                        } catch (\Throwable $e) {
                                                            \Log::error('performance_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open(['method' => 'DELETE', 'url' => $delUrl, 'id' => $formId, 'data-url'=>$delUrl, 'data-sv-localized'=>'true', 'data-guard-msg'=>$delGuard]) !!}
                                                            <a href="{{ $delUrl }}"
                                                               class="{{ VC::BT_SM_CT_PR }}"
                                                               data-url="{{ $delUrl }}"
                                                               data-sv-localized="true"
                                                               data-guard-msg="{{ base64_encode($delGuard) }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Delete') }}"
                                                               data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                               data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
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
                                        <td colspan="2" class="{{ VC::TXCT_MT }}">{{ __('No performance types found.') }}</td>
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
    <script defer src="{{ asset('assets/js/routes/performanceTypes/index.js') }}"></script>
@endpush
