@php
    use App\Config\Constants\{ViewsConstants as VW, ExtendingLayoutsConstants, YieldingConstants, StacksConstants};
    use App\Models\Utility;
    try {
        $list = Utility::isFilled($deductions ?? null) ? $deductions : collect();
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
        \Log::error('saturation_deductions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $list ??= collect();
    $dashboardUrl ??= '#';
    $dashboardGuardMessage ??= '';
    $dashboardLinkId ??= 'breadcrumb-dashboard-link';
    $dashboardResolvedRouteName ??= 'home';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Saturation Deduction') }}
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
    <li class="{{ VC::BCI }}">{{ __('Saturation Deduction') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create saturation deduction')
            @php
                $satDeductionCreateLinkId       = 'saturation-deduction-create-link';
                $satDeductionCreateUrl          = '#';
                $satDeductionCreateGuardMessage = 'Create saturation deduction route is unavailable. Please contact technical support or your domain administrator.';
                try {
                    $satDeductionCreateBaseRouteName   = VW::STR_DD.'.create';
                    $satDeductionCreateKebabRouteName  = Str::kebab($satDeductionCreateBaseRouteName);
                    $satDeductionCreateResolvedName    = Route::has($satDeductionCreateBaseRouteName)
                        ? $satDeductionCreateBaseRouteName
                        : (Route::has($satDeductionCreateKebabRouteName) ? $satDeductionCreateKebabRouteName : null);
                    $satDeductionCreateUrl             = $satDeductionCreateResolvedName ? route($satDeductionCreateResolvedName) : '#';
                    $satDeductionCreateGuardMessage    = (class_exists(Utility::class)
                        ? Utility::fetchLinkMessage($langValue, VW::STR_DD, 'create_saturation_deduction_route_unavailable')
                        : null) ?? 'Create saturation deduction route is unavailable. Please contact technical support or your domain administrator.';
                    $satDeductionCreateLinkId          = 'saturation-deduction-create-link';
                } catch (\Throwable $e) {
                    \Log::error('saturation_deductions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a id="{{ $satDeductionCreateLinkId }}"
               href="{{ $satDeductionCreateUrl }}"
               data-url="{{ $satDeductionCreateUrl }}"
               data-guard-msg="{{ base64_encode($satDeductionCreateGuardMessage) }}"
               data-sv-localized="true"
               data-ajax-popup="true"
               data-title="{{ __('Create New Saturation Deduction') }}"
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
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Deduction Option') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse ($list as $deduction)
                                    @php
                                        $satDedIdValue = (string) ($deduction->id ?? '');
@endphp
                                    <tr>
                                        <td>{{ $deduction->employee->name ?? __('N/A') }}</td>
                                        <td>{{ $deduction->deductionOption->name ?? __('N/A') }}</td>
                                        <td>{{ $deduction->title ?? __('N/A') }}</td>
                                        <td>{{ ucfirst($deduction->type?->value ?? ($deduction->type ?? __('N/A'))) }}</td>
                                        <td>{{ number_format((float) ($deduction->amount ?? 0), 2) }}</td>
                                        <td>
                                            @can('edit saturation deduction')
                                                @php
                                                    try {
                                                        $satDeductionEditBaseRouteName   = VW::STR_DD.'.edit';
                                                        $satDeductionEditKebabRouteName  = Str::kebab($satDeductionEditBaseRouteName);
                                                        $satDeductionEditResolvedName    = Route::has($satDeductionEditBaseRouteName)
                                                            ? $satDeductionEditBaseRouteName
                                                            : (Route::has($satDeductionEditKebabRouteName) ? $satDeductionEditKebabRouteName : null);
                                                        $satDeductionEditUrl             = ($satDeductionEditResolvedName && $satDedIdValue !== '')
                                                            ? route($satDeductionEditResolvedName, $satDedIdValue)
                                                            : '#';
                                                        $satDeductionEditGuardMessage    = (class_exists(Utility::class)
                                                            ? Utility::fetchLinkMessage($langValue, VW::STR_DD, 'edit_saturation_deduction_route_unavailable')
                                                            : null) ?? 'Edit saturation deduction route is unavailable. Please contact technical support or your domain administrator.';
                                                        $satDeductionEditLinkId          = 'saturation-deduction-edit-link-'.($satDedIdValue === '' ? 'x' : $satDedIdValue);
                                                    } catch (\Throwable $e) {
                                                        \Log::error('saturation_deductions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a id="{{ $satDeductionEditLinkId }}"
                                                       href="{{ $satDeductionEditUrl }}"
                                                       class="{{ VC::BT_SM_CT }}"
                                                       data-url="{{ $satDeductionEditUrl }}"
                                                       data-guard-msg="{{ base64_encode($satDeductionEditGuardMessage) }}"
                                                       data-sv-localized="true"
                                                       data-ajax-popup="true"
                                                       data-title="{{ __('Edit Saturation Deduction') }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Edit') }}">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('delete saturation deduction')
                                                @php
                                                    try {
                                                        $satDeductionDestroyBaseRouteName   = VW::STR_DD.'.destroy';
                                                        $satDeductionDestroyKebabRouteName  = Str::kebab($satDeductionDestroyBaseRouteName);
                                                        $satDeductionDestroyResolvedName    = Route::has($satDeductionDestroyBaseRouteName)
                                                            ? $satDeductionDestroyBaseRouteName
                                                            : (Route::has($satDeductionDestroyKebabRouteName) ? $satDeductionDestroyKebabRouteName : null);
                                                        $satDeductionDestroyUrl             = ($satDeductionDestroyResolvedName && $satDedIdValue !== '')
                                                            ? route($satDeductionDestroyResolvedName, $satDedIdValue)
                                                            : '#';
                                                        $satDeductionDestroyGuardMessage    = (class_exists(Utility::class)
                                                            ? Utility::fetchLinkMessage($langValue, VW::STR_DD, 'destroy_saturation_deduction_route_unavailable')
                                                            : null) ?? 'Delete saturation deduction route is unavailable. Please contact technical support or your domain administrator.';
                                                        $satDeductionDeleteFormId           = 'saturation-deduction-delete-form-'.($satDedIdValue === '' ? 'x' : $satDedIdValue);
                                                        $satDeductionDeleteLinkId           = 'saturation-deduction-delete-link-'.($satDedIdValue === '' ? 'x' : $satDedIdValue);
                                                    } catch (\Throwable $e) {
                                                        \Log::error('saturation_deductions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method'            => 'DELETE',
                                                        'url'               => $satDeductionDestroyUrl,
                                                        'id'                => $satDeductionDeleteFormId,
                                                        'data-url'          => $satDeductionDestroyUrl,
                                                        'data-guard-msg'    => $satDeductionDestroyGuardMessage,
                                                        'data-sv-localized' => 'true',
                                                    ]) !!}
                                                        <a id="{{ $satDeductionDeleteLinkId }}"
                                                           href="#"
                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __(class_exists(Utility::class) ? (Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?') : 'Are You Sure?') }}|{{ __(class_exists(Utility::class) ? (Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') : 'This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('{{ $satDeductionDeleteFormId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            window.RouteGuard?.guardMultiple?.('{{ $satDeductionEditLinkId ?? '' }}', '{{ $satDeductionDeleteLinkId ?? '' }}');
                                            window.RouteGuard?.guardFormSubmit?.('{{ $satDeductionDeleteFormId ?? '' }}');
                                        </script>
                                    @endpush
                                @empty
                                    <tr>
                                        <td colspan="6" class="{{ VC::TXCT_MT }} {{ VC::PY4 }}">
                                            {{ __('No saturation deductions found.') }}
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
        @can('create saturation deduction')
            window.RouteGuard?.guardById?.('{{ $satDeductionCreateLinkId ?? '' }}');
        @endcan
    </script>
@endpush
