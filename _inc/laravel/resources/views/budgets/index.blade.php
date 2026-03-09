@php
    try {
$lang = Utility::fetchUserLang();
    } catch (\Throwable $e) {
        \Log::error('budgets/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Budget Planner')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Budget Planner')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @can('create budget plan')
        <div class="{{ VC::FEND }}">
            @php
                try {
                    $budgetCreateRoute = Route::has(ViewsConstants::BDG . '.create')
                        ? route(ViewsConstants::BDG . '.create', 0)
                        : '#';
                    $budgetCreateBtnId = 'budget-planner-create-btn';
                    $budgetCreateMsg   = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::BDG,
                        'budget_planner_create_route_unavailable'
                    ) ?? 'Budget Planner create route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('budgets/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a
                id="{{ $budgetCreateBtnId }}"
                href="{{ $budgetCreateRoute }}"
                data-url="{{ $budgetCreateRoute }}"
                data-guard-msg="{{ base64_encode($budgetCreateMsg) }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/budgets/create.js') }}"></script>
            @endpush
        </div>
    @endcan
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('From') }}</th>
                                    {{-- <th>{{ __('To') }}</th> --}}
                                    <th>{{ __('Budget Period') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    try {
                                        $hasBudgets =
                                            (is_array($budgets ?? null) && count($budgets ?? []) > 0)
                                            || (($budgets ?? null) instanceof Collection && ($budgets)->isNotEmpty());
                                    } catch (\Throwable $e) {
                                        \Log::error('budgets/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp

                                @if($hasBudgets)
                                    @foreach ($budgets as $budget)
                                        @php
                                            try {
                                                $budgetId         = data_get($budget, 'id');
                                                $idSuffix         = !empty($budgetId) ? $budgetId : 'na';

                                                $nameRaw          = data_get($budget, 'name');
                                                $nameOut          = isset($nameRaw) && $nameRaw !== '' ? (string) $nameRaw : __('No budget name available');

                                                $fromRaw          = data_get($budget, 'from');
                                                $fromOut          = isset($fromRaw) && $fromRaw !== '' ? (string) $fromRaw : __('No start date available');

                                                $periodIndex      = data_get($budget, 'period');
                                                $periodMap        = \App\Models\Budget::$frequency ?? [];
                                                $hasPeriodLabel   = isset($periodIndex) && is_array($periodMap) && array_key_exists($periodIndex, $periodMap);
                                                $periodOut        = $hasPeriodLabel ? __((string) $periodMap[$periodIndex]) : __('No budget period available');
                                            } catch (\Throwable $e) {
                                                \Log::error('budgets/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp

                                        <tr>
                                            <td class="font-style">{{ $nameOut }}</td>
                                            <td class="font-style">{{ $fromOut }}</td>
                                            {{-- <td class="font-style">{{ $toOut }}</td> --}}
                                            <td class="font-style">{{ $periodOut }}</td>

                                            <td class="Action">
                                                <span>
                                                    @can('edit budget plan')
                                                        @php
                                                            try {
                                                                $langLocal      = \App\Models\Utility::fetchUserLang();
                                                                $encryptedId    = !empty($budgetId) ? Crypt::encrypt($budgetId) : null;
                                                                $planEditRoute  = ($encryptedId && Route::has(ViewsConstants::BDG . '.edit'))
                                                                    ? route(ViewsConstants::BDG . '.edit', $encryptedId)
                                                                    : '#';
                                                                $editBtnId      = 'budget-edit-btn-' . $idSuffix;
                                                                $planEditMsg    = \App\Models\Utility::fetchLinkMessage(
                                                                    $langLocal,
                                                                    ViewsConstants::BDG,
                                                                    'budget_plan_edit_route_unavailable'
                                                                ) ?? 'Edit route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('budgets/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a
                                                                id="{{ $editBtnId }}"
                                                                href="{{ $planEditRoute }}"
                                                                data-url="{{ $planEditRoute }}"
                                                                data-guard-msg="{{ base64_encode($planEditMsg) }}"
                                                                class="{{ VC::BT_SM_CT }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Edit') }}"
                                                                data-original-title="{{ __('Edit') }}"
                                                            >
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('view budget plan')
                                                        @php
                                                            try {
                                                                $langLocal      = \App\Models\Utility::fetchUserLang();
                                                                $encryptedId    = !empty($budgetId) ? Crypt::encrypt($budgetId) : null;
                                                                $planViewRoute  = ($encryptedId && Route::has(ViewsConstants::BDG . '.show'))
                                                                    ? route(ViewsConstants::BDG . '.show', $encryptedId)
                                                                    : '#';
                                                                $viewBtnId      = 'budget-view-btn-' . $idSuffix;
                                                                $planViewMsg    = \App\Models\Utility::fetchLinkMessage(
                                                                    $langLocal,
                                                                    ViewsConstants::BDG,
                                                                    'budget_plan_view_route_unavailable'
                                                                ) ?? 'View route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('budgets/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_INF }}">
                                                            <a
                                                                id="{{ $viewBtnId }}"
                                                                href="{{ $planViewRoute }}"
                                                                data-url="{{ $planViewRoute }}"
                                                                data-guard-msg="{{ base64_encode($planViewMsg) }}"
                                                                class="{{ VC::BT_SM_CT }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('View') }}"
                                                                data-original-title="{{ __('Detail') }}"
                                                            >
                                                                <i class="{{ VC::TI_EYE_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('delete budget plan')
                                                        @php
                                                            try {
                                                                $langLocal         = \App\Models\Utility::fetchUserLang();
                                                                $planDestroyRoute  = (!empty($budgetId) && Route::has(ViewsConstants::BDG . '.destroy'))
                                                                    ? route(ViewsConstants::BDG . '.destroy', $budgetId)
                                                                    : '#';
                                                                $destroyFormId     = 'delete-form-' . $idSuffix;
                                                                $destroyBtnId      = 'budget-delete-btn-' . $idSuffix;
                                                                $planDestroyMsg    = \App\Models\Utility::fetchLinkMessage(
                                                                    $langLocal,
                                                                    ViewsConstants::BDG,
                                                                    'budget_plan_destroy_route_unavailable'
                                                                ) ?? 'Failed to get destroy route';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('budgets/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Form::open([
                                                                'url'            => $planDestroyRoute,
                                                                'method'         => 'DELETE',
                                                                'id'             => $destroyFormId,
                                                                'data-url'       => $planDestroyRoute,
                                                                'data-guard-msg' => $planDestroyMsg,
                                                            ]) !!}
                                                                <a
                                                                    id="{{ $destroyBtnId }}"
                                                                    href="#"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Delete') }}"
                                                                    data-original-title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __(\App\Models\Utility::fetchLinkMessage($langLocal, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(\App\Models\Utility::fetchLinkMessage($langLocal, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('{{ $destroyFormId }}').submit();"
                                                                >
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="{{ VC::TXCT_DK }}">{{ __('No budget records available') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push(StacksConstants::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/budgets/index.js') }}"></script>
    @endpush
@endsection
