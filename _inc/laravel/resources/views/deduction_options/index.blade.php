@php
    try {
$list = Utility::isFilled($deductionoptions) ? $deductionoptions : ['' => __('No deduction options available' ?? [])];
        $langValue = $lang ?? (class_exists(Utility::class) ? Utility::fetchUserLang() : null);
        $dashboardBaseRouteName     = 'dashboard';
        $dashboardKebabRouteName    = Str::kebab($dashboardBaseRouteName);
        $dashboardResolvedRouteName = Route::has($dashboardBaseRouteName)
            ? $dashboardBaseRouteName
            : (Route::has($dashboardKebabRouteName) ? $dashboardKebabRouteName : null);
        $dashboardUrl               = $dashboardResolvedRouteName ? route($dashboardResolvedRouteName) : '#';
        $dashboardGuardMessage      = (class_exists(Utility::class)
            ? Utility::fetchLinkMessage($langValue, 'generics', 'dashboard_unavailable')
            : null) ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
        $dashboardLinkId            = 'breadcrumb-dashboard-link';
    } catch (\Throwable $e) {
        \Log::error('deduction_options/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $list ??= collect();
    $dashboardUrl ??= '#';
    $dashboardGuardMessage ??= '';
    $dashboardLinkId ??= 'breadcrumb-dashboard-link';
    $dashboardResolvedRouteName ??= 'home';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Deduction Option') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a id="{{ $dashboardLinkId }}"
           href="{{ $dashboardUrl }}"
           data-url="{{ $dashboardUrl }}"
           data-guard-msg="{{ base64_encode($dashboardGuardMessage) }}"
           data-sv-localized="true"
           {{ $dashboardResolvedRouteName ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Deduction Option') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create document type')
            @php
                $deductionOptionCreateLinkId       = 'deduction-option-create-link';
                $deductionOptionCreateUrl          = '#';
                $deductionOptionCreateGuardMessage = 'Create deduction option route is unavailable. Please contact technical support or your domain administrator.';
                try {
                    $deductionOptionCreateBaseRouteName   = VW::DDT_OPT.'.create';
                    $deductionOptionCreateKebabRouteName  = Str::kebab($deductionOptionCreateBaseRouteName);
                    $deductionOptionCreateResolvedName    = Route::has($deductionOptionCreateBaseRouteName)
                        ? $deductionOptionCreateBaseRouteName
                        : (Route::has($deductionOptionCreateKebabRouteName) ? $deductionOptionCreateKebabRouteName : null);
                    $deductionOptionCreateUrl             = $deductionOptionCreateResolvedName ? route($deductionOptionCreateResolvedName) : '#';
                    $deductionOptionCreateGuardMessage    = (class_exists(Utility::class)
                        ? Utility::fetchLinkMessage($langValue, VW::DDT_OPT, 'create_deduction_option_route_unavailable')
                        : null) ?? 'Create deduction option route is unavailable. Please contact technical support or your domain administrator.';
                    $deductionOptionCreateLinkId          = 'deduction-option-create-link';
                } catch (\Throwable $e) {
                    \Log::error('deduction_options/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a id="{{ $deductionOptionCreateLinkId }}"
               href="{{ $deductionOptionCreateUrl }}"
               data-url="{{ $deductionOptionCreateUrl }}"
               data-guard-msg="{{ base64_encode($deductionOptionCreateGuardMessage) }}"
               data-sv-localized="true"
               data-ajax-popup="true"
               data-title="{{ __('Create New Deduction Option') }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="{{ VC::C3 }}">
            @include('layouts.hrm_setup')
        </div>
        <div class="{{ VC::C9 }}">
            <div class="card">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Deduction Option') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse ($list as $deductionoption)
                                    @php
                                        $dedOptIdValue = (string) ($deductionoption->id ?? '');
@endphp
                                    <tr>
                                        <td>{{ !empty($deductionoption->name) ? $deductionoption->name : __('No name available for Deduction option') }}</td>
                                        <td>
                                            @can('edit deduction option')
                                                @php
                                                    try {
                                                        $deductionOptionEditBaseRouteName   = VW::DDT_OPT.'.edit';
                                                        $deductionOptionEditKebabRouteName  = Str::kebab($deductionOptionEditBaseRouteName);
                                                        $deductionOptionEditResolvedName    = Route::has($deductionOptionEditBaseRouteName)
                                                            ? $deductionOptionEditBaseRouteName
                                                            : (Route::has($deductionOptionEditKebabRouteName) ? $deductionOptionEditKebabRouteName : null);
                                                        $deductionOptionEditUrl             = ($deductionOptionEditResolvedName && $dedOptIdValue !== '')
                                                            ? route($deductionOptionEditResolvedName, $dedOptIdValue)
                                                            : '#';
                                                        $deductionOptionEditGuardMessage    = (class_exists(Utility::class)
                                                            ? Utility::fetchLinkMessage($langValue, VW::DDT_OPT, 'edit_deduction_option_route_unavailable')
                                                            : null) ?? 'Edit deduction option route is unavailable. Please contact technical support or your domain administrator.';
                                                        $deductionOptionEditLinkId          = 'deduction-option-edit-link-'.($dedOptIdValue === '' ? 'x' : $dedOptIdValue);
                                                    } catch (\Throwable $e) {
                                                        \Log::error('deduction_options/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a id="{{ $deductionOptionEditLinkId }}"
                                                       href="{{ $deductionOptionEditUrl }}"
                                                       class="{{ VC::BT_SM_CT }}"
                                                       data-url="{{ $deductionOptionEditUrl }}"
                                                       data-guard-msg="{{ base64_encode($deductionOptionEditGuardMessage) }}"
                                                       data-sv-localized="true"
                                                       data-ajax-popup="true"
                                                       data-title="{{ __('Edit Deduction Option') }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Edit') }}">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('delete deduction option')
                                                @php
                                                    try {
                                                        $deductionOptionDestroyBaseRouteName   = VW::DDT_OPT.'.destroy';
                                                        $deductionOptionDestroyKebabRouteName  = Str::kebab($deductionOptionDestroyBaseRouteName);
                                                        $deductionOptionDestroyResolvedName    = Route::has($deductionOptionDestroyBaseRouteName)
                                                            ? $deductionOptionDestroyBaseRouteName
                                                            : (Route::has($deductionOptionDestroyKebabRouteName) ? $deductionOptionDestroyKebabRouteName : null);
                                                        $deductionOptionDestroyUrl             = ($deductionOptionDestroyResolvedName && $dedOptIdValue !== '')
                                                            ? route($deductionOptionDestroyResolvedName, $dedOptIdValue)
                                                            : '#';
                                                        $deductionOptionDestroyGuardMessage    = (class_exists(Utility::class)
                                                            ? Utility::fetchLinkMessage($langValue, VW::DDT_OPT, 'destroy_deduction_option_route_unavailable')
                                                            : null) ?? 'Delete deduction option route is unavailable. Please contact technical support or your domain administrator.';
                                                        $deductionOptionDeleteFormId           = 'deduction-option-delete-form-'.($dedOptIdValue === '' ? 'x' : $dedOptIdValue);
                                                        $deductionOptionDeleteLinkId           = 'deduction-option-delete-link-'.($dedOptIdValue === '' ? 'x' : $dedOptIdValue);
                                                    } catch (\Throwable $e) {
                                                        \Log::error('deduction_options/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method'            => 'DELETE',
                                                        'url'               => $deductionOptionDestroyUrl,
                                                        'id'                => $deductionOptionDeleteFormId,
                                                        'data-url'          => $deductionOptionDestroyUrl,
                                                        'data-guard-msg'    => $deductionOptionDestroyGuardMessage,
                                                        'data-sv-localized' => 'true',
                                                    ]) !!}
                                                        <a id="{{ $deductionOptionDeleteLinkId }}"
                                                           href="#"
                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __(class_exists(Utility::class) ? (Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?') : 'Are You Sure?') }}|{{ __(class_exists(Utility::class) ? (Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') : 'This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('{{ $deductionOptionDeleteFormId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            window.RouteGuard?.guardMultiple?.('{{ $deductionOptionEditLinkId ?? '' }}', '{{ $deductionOptionDeleteLinkId ?? '' }}');
                                            window.RouteGuard?.guardFormSubmit?.('{{ $deductionOptionDeleteFormId ?? '' }}');
                                        </script>
                                    @endpush
                                @empty
                                    <tr>
                                        <td colspan="2" class="{{ VC::TXCT_MT }} {{ VC::PY4 }}">
                                            {{ __('No deduction options found.') }}
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

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        window.RouteGuard?.guardById?.('{{ $dashboardLinkId }}');
        @can('create document type')
            window.RouteGuard?.guardById?.('{{ $deductionOptionCreateLinkId ?? '' }}');
        @endcan
    </script>
@endpush
