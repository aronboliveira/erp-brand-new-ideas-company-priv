@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Collection;

    $lang        = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:Auth::user()) : app()->getLocale();
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);

    $dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
    $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $items = [];
    if (is_array($units ?? null) && count($units)) {
        $items = $units;
    } elseif (($units ?? null) instanceof Collection && $units->isNotEmpty()) {
        $items = $units;
    }
@endphp

@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Product & Service Unit') }}
@endsection

@section(YD::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ $dashGuard }}"
           {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Unit') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create constant unit')
            @php
                $createUrl   = Route::has(VW::PRD_SV_UNT.'.create') ? route(VW::PRD_SV_UNT.'.create') : '#';
                $createGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV_UNT, 'create_unit_unavailable') : 'Create Unit route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Unit route is unavailable. Please contact technical support or your domain administrator.');
            @endphp
            <a href="{{ $createUrl }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-title="{{ __('Create New Unit') }}"
               data-sv-localized="true"
               data-guard-msg="{{ $createGuard }}"
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
        <div class="{{ VC::CL3 }}">
            @include('layouts.account_setup')
        </div>
        <div class="{{ VC::CL9 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Unit') }}</th>
                                    @canany(['edit constant category','delete constant category'])
                                        <th width="10%">{{ __('Action') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $unit)
                                    @php
                                        $unitName = isset($unit->name) && $unit->name !== '' ? $unit->name : __('No available name for unit');
                                    @endphp
                                    <tr>
                                        <td>{{ $unitName }}</td>
                                        @canany(['edit constant category','delete constant category'])
                                            <td class="Action">
                                                <span>
                                                    @can('edit constant category')
                                                        @php
                                                            $editUrl   = Route::has(VW::PRD_SV_UNT.'.edit') ? route(VW::PRD_SV_UNT.'.edit', $unit->id) : '#';
                                                            $editGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV_UNT, 'edit_unit_unavailable') : 'Edit Unit route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Unit route is unavailable. Please contact technical support or your domain administrator.');
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a href="{{ $editUrl }}"
                                                               class="{{ VC::BT_SM_CT }}"
                                                               data-url="{{ $editUrl }}"
                                                               data-ajax-popup="true"
                                                               data-title="{{ __('Edit Unit') }}"
                                                               data-sv-localized="true"
                                                               data-guard-msg="{{ $editGuard }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Edit') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('delete constant category')
                                                        @php
                                                            $delUrl   = Route::has(VW::PRD_SV_UNT.'.destroy') ? route(VW::PRD_SV_UNT.'.destroy', $unit->id) : '#';
                                                            $delGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV_UNT, 'delete_unit_unavailable') : 'Delete Unit route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete Unit route is unavailable. Please contact technical support or your domain administrator.');
                                                            $formId   = 'delete-unit-form-'.$unit->id;
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Form::open(['method' => 'DELETE', 'url' => $delUrl, 'id' => $formId, 'data-url'=>$delUrl, 'data-sv-localized'=>'true', 'data-guard-msg'=>$delGuard]) !!}
                                                                <a href="{{ $delUrl }}"
                                                                   class="{{ VC::BT_SM_CT_PR }}"
                                                                   data-url="{{ $delUrl }}"
                                                                   data-sv-localized="true"
                                                                   data-guard-msg="{{ $delGuard }}"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Delete') }}"
                                                                   data-original-title="{{ __('Delete') }}"
                                                                   data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                   data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </span>
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">{{ __('No units found.') }}</td>
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
    <script defer src="{{ asset('assets/js/routes/products/service/units/index.js') }}"></script>
@endpush
