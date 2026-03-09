@php
    use App\Config\Constants\{ViewsConstants as VW, ExtendingLayoutsConstants, YieldingConstants, StacksConstants};
    use App\Models\Utility;
    try {
        $list = Utility::isFilled($commissions ?? null) ? $commissions : collect();
        $langValue = $lang ?? (class_exists(Utility::class) ? Utility::fetchUserLang() : null);
        $dashboardUrl = Route::has('dashboard') ? route('dashboard') : '#';
        $dashboardGuardMessage = (class_exists(Utility::class)
            ? Utility::fetchLinkMessage($langValue, 'generics', 'dashboard_unavailable')
            : null) ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('commissions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $list ??= collect();
    $dashboardUrl ??= '#';
    $dashboardGuardMessage ??= '';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Commissions') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ $dashboardUrl }}"
           data-url="{{ $dashboardUrl }}"
           data-guard-msg="{{ base64_encode($dashboardGuardMessage) }}"
           data-sv-localized="true"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Commissions') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create commission')
            @php
                try {
                    $createRoute = VW::COM . '.create';
                    $createUrl = Route::has($createRoute) ? route($createRoute) : (Route::has(Str::kebab($createRoute)) ? route(Str::kebab($createRoute)) : '#');
                } catch (\Throwable $e) {
                    \Log::error('commissions/index create route — ' . $e->getMessage());
                }
                $createUrl ??= '#';
            @endphp
            <a href="{{ $createUrl }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-title="{{ __('Create New Commission') }}"
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
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse ($list as $commission)
                                    @php $itemId = (string) ($commission->id ?? ''); @endphp
                                    <tr>
                                        <td>{{ $commission->employee->name ?? __('N/A') }}</td>
                                        <td>{{ $commission->title ?? __('N/A') }}</td>
                                        <td>{{ ucfirst($commission->type?->value ?? ($commission->type ?? __('N/A'))) }}</td>
                                        <td>{{ number_format((float) ($commission->amount ?? 0), 2) }}</td>
                                        <td>
                                            @can('edit commission')
                                                @php
                                                    try {
                                                        $editRoute = VW::COM . '.edit';
                                                        $editUrl = (Route::has($editRoute) && $itemId !== '') ? route($editRoute, $itemId) : '#';
                                                    } catch (\Throwable $e) { $editUrl = '#'; }
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a href="{{ $editUrl }}"
                                                       class="{{ VC::BT_SM_CT }}"
                                                       data-url="{{ $editUrl }}"
                                                       data-ajax-popup="true"
                                                       data-title="{{ __('Edit Commission') }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Edit') }}">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete commission')
                                                @php
                                                    try {
                                                        $destroyRoute = VW::COM . '.destroy';
                                                        $destroyUrl = (Route::has($destroyRoute) && $itemId !== '') ? route($destroyRoute, $itemId) : '#';
                                                        $deleteFormId = 'commission-delete-form-' . ($itemId ?: 'x');
                                                    } catch (\Throwable $e) { $destroyUrl = '#'; $deleteFormId = 'commission-delete-form-x'; }
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'url' => $destroyUrl, 'id' => $deleteFormId]) !!}
                                                        <a href="#"
                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __('Are You Sure?') }}|{{ __('This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="{{ VC::TXCT_MT }} {{ VC::PY4 }}">
                                            {{ __('No commissions found.') }}
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
