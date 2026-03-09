@php
    use App\Config\Constants\{ViewsConstants as VW, ExtendingLayoutsConstants, YieldingConstants, StacksConstants};
    use App\Models\Utility;
    try {
        $list = Utility::isFilled($loans ?? null) ? $loans : collect();
        $langValue = $lang ?? (class_exists(Utility::class) ? Utility::fetchUserLang() : null);
        $dashboardUrl = Route::has('dashboard') ? route('dashboard') : '#';
        $dashboardGuardMessage = (class_exists(Utility::class)
            ? Utility::fetchLinkMessage($langValue, 'generics', 'dashboard_unavailable')
            : null) ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('loans/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $list ??= collect();
    $dashboardUrl ??= '#';
    $dashboardGuardMessage ??= '';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Loans') }}
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
    <li class="{{ VC::BCI }}">{{ __('Loans') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create loan')
            @php
                try {
                    $createRoute = VW::LN . '.create';
                    $createUrl = Route::has($createRoute) ? route($createRoute) : (Route::has(Str::kebab($createRoute)) ? route(Str::kebab($createRoute)) : '#');
                } catch (\Throwable $e) {
                    \Log::error('loans/index create route — ' . $e->getMessage());
                }
                $createUrl ??= '#';
            @endphp
            <a href="{{ $createUrl }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-title="{{ __('Create New Loan') }}"
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
                                    <th>{{ __('Loan Option') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse ($list as $loan)
                                    @php $itemId = (string) ($loan->id ?? ''); @endphp
                                    <tr>
                                        <td>{{ $loan->employee->name ?? __('N/A') }}</td>
                                        <td>{{ $loan->loanOption->name ?? __('N/A') }}</td>
                                        <td>{{ $loan->title ?? __('N/A') }}</td>
                                        <td>{{ ucfirst($loan->type?->value ?? ($loan->type ?? __('N/A'))) }}</td>
                                        <td>{{ number_format((float) ($loan->amount ?? 0), 2) }}</td>
                                        <td>{{ $loan->start_date ?? __('N/A') }}</td>
                                        <td>{{ $loan->end_date ?? __('N/A') }}</td>
                                        <td>
                                            @can('edit loan')
                                                @php
                                                    try {
                                                        $editRoute = VW::LN . '.edit';
                                                        $editUrl = (Route::has($editRoute) && $itemId !== '') ? route($editRoute, $itemId) : '#';
                                                    } catch (\Throwable $e) { $editUrl = '#'; }
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a href="{{ $editUrl }}"
                                                       class="{{ VC::BT_SM_CT }}"
                                                       data-url="{{ $editUrl }}"
                                                       data-ajax-popup="true"
                                                       data-title="{{ __('Edit Loan') }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Edit') }}">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete loan')
                                                @php
                                                    try {
                                                        $destroyRoute = VW::LN . '.destroy';
                                                        $destroyUrl = (Route::has($destroyRoute) && $itemId !== '') ? route($destroyRoute, $itemId) : '#';
                                                        $deleteFormId = 'loan-delete-form-' . ($itemId ?: 'x');
                                                    } catch (\Throwable $e) { $destroyUrl = '#'; $deleteFormId = 'loan-delete-form-x'; }
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
                                        <td colspan="8" class="{{ VC::TXCT_MT }} {{ VC::PY4 }}">
                                            {{ __('No loans found.') }}
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
