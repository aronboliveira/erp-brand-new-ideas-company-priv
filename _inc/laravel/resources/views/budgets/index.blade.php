@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Budget Planner')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Budget Planner')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @can('create budget plan')
        <div class="float-end">
            @php
                $budgetCreateRoute = Route::has(ViewsConstants::BDG . '.create')
                    ? route(ViewsConstants::BDG . '.create', 0)
                    : '#';
                $budgetCreateBtnId = 'budget-planner-create-btn';
                $budgetCreateMsg   = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::BDG,
                    'budget_planner_create_route_unavailable'
                ) ?? 'Budget Planner create route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                id="{{ $budgetCreateBtnId }}"
                href="{{ $budgetCreateRoute }}"
                data-url="{{ $budgetCreateRoute }}"
                data-guard-msg="{{ $budgetCreateMsg }}"
                class="btn btn-sm btn-primary"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
            >
                <i class="ti ti-plus"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const btn = document.getElementById('{{ $budgetCreateBtnId }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active', 'true');
            
                        btn.addEventListener('click', event => {
                            try {
                                const href = btn.getAttribute('href');
                                const url  = btn.getAttribute('data-url');
                                if ((href && href !== '#') || (url && url !== '#')) return;
                                event.preventDefault();
                                const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                let container       = document.getElementById('toast-container');
                                if (!container) {
                                    container       = document.createElement('div');
                                    container.id    = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bootstrapLink && window.bootstrap) {
                                    const toastEl      = document.createElement('div');
                                    toastEl.className  = 'toast';
                                    toastEl.setAttribute('role', 'alert');
                                    toastEl.setAttribute('aria-live', 'assertive');
                                    toastEl.setAttribute('aria-atomic', 'true');
                                    const body         = document.createElement('div');
                                    body.className     = 'toast-body';
                                    body.textContent   = msg;
                                    toastEl.appendChild(body);
                                    container.appendChild(toastEl);
                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                } else {
                                    alert(msg);
                                }
                                btn.setAttribute('data-failed-route', 'true');
                            } catch (e) {}
                        });
                    })();
                </script>
            @endpush
        </div>
    @endcan
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{__('Name')}}</th>
                                <th> {{__('From')}}</th>
                                {{--                                <th> {{__('To')}}</th>--}}
                                <th> {{__('Budget Period')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($budgets as $budget)
                                <tr>
                                    <td class="font-style">{{ $budget->name }}</td>
                                    <td class="font-style">{{ $budget->from }}</td>
                                    {{--                                    <td class="font-style">{{ $budget->to }}</td>--}}
                                    <td class="font-style">{{ __(\App\Models\Budget::$period[$budget->period]) }}</td>
                                    @php
                                        $planEditRoute          = Route::has(ViewsConstants::BDG . '.edit')
                                            ? route(ViewsConstants::BDG . '.edit', Crypt::encrypt($budget->id))
                                            : '#';
                                        $planViewRoute          = Route::has(ViewsConstants::BDG . '.show')
                                            ? route(ViewsConstants::BDG . '.show', Crypt::encrypt($budget->id))
                                            : '#';
                                        $planDestroyRoute       = Route::has(ViewsConstants::BDG . '.destroy')
                                            ? route(ViewsConstants::BDG . '.destroy', $budget->id)
                                            : '#';
                                    
                                        $editBtnId              = 'budget-edit-btn-' . $budget->id;
                                        $viewBtnId              = 'budget-view-btn-' . $budget->id;
                                        $destroyBtnId           = 'budget-delete-btn-' . $budget->id;
                                        $destroyFormId          = 'delete-form-' . $budget->id;
                                    
                                        $planEditMsg            = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::BDG,
                                            'budget_plan_edit_route_unavailable'
                                        ) ?? 'Edit route is unavailable. Please contact technical support or your domain administrator.';
                                        $planViewMsg            = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::BDG,
                                            'budget_plan_view_route_unavailable'
                                        ) ?? 'View route is unavailable. Please contact technical support or your domain administrator.';
                                        $planDestroyMsg         = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::BDG,
                                            'budget_plan_destroy_route_unavailable'
                                        ) ?? 'budget_plan_destroy_route_unavailable'
                                    @endphp
                                <td class="Action">
                                    <span>
                                        @can('edit budget plan')
                                            <div class="action-btn bg-primary ms-2">
                                                <a
                                                    id="{{ $editBtnId }}"
                                                    href="{{ $planEditRoute }}"
                                                    data-url="{{ $planEditRoute }}"
                                                    data-guard-msg="{{ $planEditMsg }}"
                                                    class="mx-3 btn btn-sm align-items-center"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Edit') }}"
                                                    data-original-title="{{ __('Edit') }}"
                                                >
                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                </a>
                                            </div>
                                        @endcan
                                        @can('view budget plan')
                                            <div class="action-btn bg-info ms-2">
                                                <a
                                                    id="{{ $viewBtnId }}"
                                                    href="{{ $planViewRoute }}"
                                                    data-url="{{ $planViewRoute }}"
                                                    data-guard-msg="{{ $planViewMsg }}"
                                                    class="mx-3 btn btn-sm align-items-center"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('View') }}"
                                                    data-original-title="{{ __('Detail') }}"
                                                >
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                        @endcan
                                        @can('delete budget plan')
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
                                                        data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="document.getElementById('{{ $destroyFormId }}').submit();"
                                                    >
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                {!! Form::close() !!}
                                            </div>
                                        @endcan
                                    </span>
                                </td>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer>
                                        (() => {
                                            const guardClick = id => {
                                                const el = document.getElementById(id);
                                                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                                                el.setAttribute('data-listener-active', 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const href = el.getAttribute('href');
                                                        const url  = el.getAttribute('data-url');
                                                        if ((href && href !== '#') || (url && url !== '#')) return;
                                                        event.preventDefault();
                                                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                        let container       = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container       = document.createElement('div');
                                                            container.id    = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (bootstrapLink && window.bootstrap) {
                                                            const toastEl      = document.createElement('div');
                                                            toastEl.className  = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body         = document.createElement('div');
                                                            body.className     = 'toast-body';
                                                            body.textContent   = msg;
                                                            toastEl.appendChild(body);
                                                            container.appendChild(toastEl);
                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        el.setAttribute('data-failed-route', 'true');
                                                    } catch (e) {}
                                                });
                                            };
                                
                                            guardClick('{{ $editBtnId }}');
                                            guardClick('{{ $viewBtnId }}');
                                            guardClick('{{ $destroyBtnId }}');
                                        })();
                                    </script>
                                @endpush
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
