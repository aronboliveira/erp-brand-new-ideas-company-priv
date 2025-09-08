@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Employee Set Salary')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route(ViewsConstants::EMP.'.index')}}">{{__('Employee')}}</a></li>
    <li class="breadcrumb-item">{{__('Employee Set Salary')}}</li>
@endsection
@section('content')
    @if(!empty($employee))
    <div class="row">
        <div class="col-12">
            <div class="row">
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::CD }} min-height-253">
                        <div class="card-header">
                            <div class="{{ VC::RW }}">
                                <div class="col">
                                    <h6 class="{{ VC::H6 }} {{ VC::MB0 }}">
                                        {{ __('Employee Salary') }}
                                    </h6>
                                </div>
                                @can('create set salary')
                                    @php
                                        $basicSalaryRoute = Route::has(ViewsConstants::EMP . '.salary.basic')
                                            ? route(ViewsConstants::EMP . '.salary.basic', $employee->id)
                                            : '#';
                                        $basicBtnId = 'salary-basic-' . $employee->id;
                                        $basicMsg   = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::EMP,
                                            'salary_basic_route_unavailable'
                                        ) ?? 'Basic salary route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <div class="col text-end">
                                        <a id="{{ $basicBtnId }}"
                                        href="{{ $basicSalaryRoute }}"
                                        data-url="{{ $basicSalaryRoute }}"
                                        data-guard-msg="{{ $basicMsg }}"
                                        data-size="md"
                                        data-ajax-popup="true"
                                        data-title="{{ __('Set Basic Salary') }}"
                                        data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('Basic Salary') }}"
                                        class="{{ VC::BT_SM_PM }}">
                                            <i class="{{ VC::TI_PLS }}"></i>
                                        </a>
                                    </div>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const btn = document.getElementById('{{ $basicBtnId }}');
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
                                                            const toastEl = document.createElement('div');
                                                            toastEl.className = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
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
                                @endcan
                            </div>
                        </div>
                        @php
                            $salaryDetails = [
                                __('Payslip Type') => optional($employee->salary_type)->name ?? '--',
                                __('Salary')       => $employee->salary       ?? '--',
                            ];
                        @endphp
                        <div class="card-body table-border-style full-card">
                            <div class="project-info {{ VC::DFL }} {{ VC::TXSM }}">
                                @foreach($salaryDetails as $label => $value)
                                <div class="project-info-inner {{ VC::ME3 }} col-6">
                                    <b class="{{ VC::MB0 }}">{{ $label }}</b>
                                    <div class="project-amnt pt-1">{{ $value }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::CD }} min-height-253">
                        <div class="{{ VC::CD }}-header">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::C12 }}">
                            <h6 class="{{ VC::MB0 }}">{{ __('Allowance') }}</h6>
                            </div>
                            @can('create allowance')
                                @php
                                    $allowanceCreateRoute = Route::has(ViewsConstants::ALW . '.create')
                                        ? route(ViewsConstants::ALW . '.create', $employee->id)
                                        : '#';
                                    $allowanceCreateBtnId = 'allowance-create-' . $employee->id;
                                    $allowanceCreateMsg   = Utility::fetchLinkMessage(
                                        $lang,
                                        ViewsConstants::ALW,
                                        'allowance_create_route_unavailable'
                                    ) ?? 'Allowance create route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                <div class="col text-end">
                                    <a
                                        id="{{ $allowanceCreateBtnId }}"
                                        href="{{ $allowanceCreateRoute }}"
                                        data-url="{{ $allowanceCreateRoute }}"
                                        data-guard-msg="{{ $allowanceCreateMsg }}"
                                        data-size="md"
                                        data-ajax-popup="true"
                                        data-title="{{ __('Create Allowance') }}"
                                        class="{{ VC::BT_SM }} {{ VC::BG_P }}"
                                        data-bs-toggle="tooltip"
                                        title="{{ __('Create') }}"
                                    >
                                        <i class="{{ VC::TI_PLS }}"></i>
                                    </a>
                                </div>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer>
                                        (() => {
                                            const btn = document.getElementById('{{ $allowanceCreateBtnId }}');
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
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
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
                            @endcan
                        </div>
                        </div>
                        <div class="{{ VC::CD }}-body {{ VC::TB }}">
                            <div class="table-responsive">
                                @if($allowances->isNotEmpty())
                                <table class="{{ VC::TB }}">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Employee Name') }}</th>
                                        <th>{{ __('Allowance Option') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                        <th>{{ __('Action') }}</th>
                                        @endif
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($allowances as $allowance)
                                        <tr>
                                        <td>{{ $employee->name }}</td>
                                        <td>{{ $allowance->allowanceOption->name ?? '' }}</td>
                                        <td>{{ $allowance->title }}</td>
                                        <td>{{ ucfirst($allowance->type) }}</td>
                                        <td>
                                            @if($allowance->type === 'fixed')
                                            {{ $user->priceFormat($allowance->amount) }}
                                            @else
                                            {{ $allowance->amount }}% ({{ $user->priceFormat($allowance->total_allow) }})
                                            @endif
                                        </td>
                                        @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                            <td>
                                                @can('edit allowance')
                                                    @php
                                                        $editAllowanceRoute = Route::has(ViewsConstants::ALW . '.edit')
                                                            ? route(ViewsConstants::ALW . '.edit', $allowance->id)
                                                            : '#';
                                                        $editAllowanceBtnId = 'allowance-edit-' . $allowance->id;
                                                        $editAllowanceMsg   = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::ALW,
                                                            'allowance_edit_route_unavailable'
                                                        ) ?? 'Allowance edit route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a id="{{ $editAllowanceBtnId }}"
                                                        href="{{ $editAllowanceRoute }}"
                                                        data-url="{{ $editAllowanceRoute }}"
                                                        data-guard-msg="{{ $editAllowanceMsg }}"
                                                        data-size="lg"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Allowance') }}"
                                                        class="{{ VC::BT_SM }} {{ VC::AL_IT_CT }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const btn = document.getElementById('{{ $editAllowanceBtnId }}');
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
                                                                            const toastEl = document.createElement('div');
                                                                            toastEl.className = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                            const body = document.createElement('div');
                                                                            body.className = 'toast-body';
                                                                            body.textContent = msg;
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
                                                @endcan
                                                @can('delete allowance')
                                                    @php
                                                        $deleteAllowanceRoute  = Route::has(ViewsConstants::ALW . '.destroy')
                                                            ? route(ViewsConstants::ALW . '.destroy', $allowance->id)
                                                            : '#';
                                                        $deleteAllowanceBtnId  = 'allowance-delete-' . $allowance->id;
                                                        $deleteAllowanceFormId = 'allowance-delete-form-' . $allowance->id;
                                                        $deleteAllowanceMsg    = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::ALW,
                                                            'allowance_destroy_route_unavailable'
                                                        ) ?? 'Allowance destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open([
                                                            'url'            => $deleteAllowanceRoute,
                                                            'method'         => 'DELETE',
                                                            'id'             => $deleteAllowanceFormId,
                                                            'data-url'       => $deleteAllowanceRoute,
                                                            'data-guard-msg' => $deleteAllowanceMsg,
                                                        ]) !!}
                                                        <a id="{{ $deleteAllowanceBtnId }}"
                                                        href="#"
                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                        data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="document.getElementById('{{ $deleteAllowanceFormId }}').submit();">
                                                            <i class="ti ti-trash {{ VC::TXT_WT }}"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const btn = document.getElementById('{{ $deleteAllowanceBtnId }}');
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
                                                                            const toastEl = document.createElement('div');
                                                                            toastEl.className = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                            const body = document.createElement('div');
                                                                            body.className = 'toast-body';
                                                                            body.textContent = msg;
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
                                                @endcan
                                            </td>
                                        @endif
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                @else
                                <div class="mt-3 text-center text-muted">
                                    {{ __('No Allowance Found!') }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::CD }} min-height-253">
                        <div class="card-header">
                            <div class="{{ VC::RW }}">
                                <div class="col">
                                    <h6 class="mb-0">{{ __('Commission') }}</h6>
                                </div>
                                @can('create commission')
                                    @php
                                        $commissionCreateRoute     = Route::has(ViewsConstants::COM . '.create')
                                            ? route(ViewsConstants::COM . '.create', $employee->id)
                                            : '#';
                                        $commissionCreateBtnId     = 'commission-create-' . $employee->id;
                                        $commissionCreateMsg       = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::COM,
                                            'commission_create_route_unavailable'
                                        ) ?? 'Commission create route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <div class="col {{ VC::JCE }}">
                                        <a
                                            id="{{ $commissionCreateBtnId }}"
                                            href="{{ $commissionCreateRoute }}"
                                            data-url="{{ $commissionCreateRoute }}"
                                            data-guard-msg="{{ $commissionCreateMsg }}"
                                            data-size="md"
                                            data-ajax-popup="true"
                                            data-title="{{ __('Create Commission') }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Create') }}"
                                            data-original-title="{{ __('Create Commission') }}"
                                            class="{{ VC::BT_SM_PM }}"
                                        >
                                            <i class="{{ VC::TI_PLS }}"></i>
                                        </a>
                                    </div>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const btn = document.getElementById('{{ $commissionCreateBtnId }}');
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
                                                            const toastEl = document.createElement('div');
                                                            toastEl.className = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
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
                                @endcan
                            </div>
                        </div>
                        <div class="{{ VC::CD_MT }} table-border-style full-card">
                            <div class="table-responsive">
                                @if(!$commissions->isEmpty())
                                    <table class="{{ VC::TB }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee Name') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($commissions as $commission)
                                                <tr>
                                                    <td>{{ $employee->name }}</td>
                                                    <td>{{ $commission->title }}</td>
                                                    <td>{{ ucfirst($commission->type) }}</td>
                                                    @if($commission->type === 'fixed')
                                                        <td>{{ $user?->priceFormat($commission->amount) }}</td>
                                                    @else
                                                        <td>{{ $commission->amount }}% (${{ $commission->tota_allow }})</td>
                                                    @endif
                                                    @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                                        <td>
                                                            @can('edit commission')
                                                                @php
                                                                    $commissionEditRoute    = Route::has(ViewsConstants::COM . '.edit')
                                                                        ? route(ViewsConstants::COM . '.edit', $commission->id)
                                                                        : '#';
                                                                    $commissionEditBtnId    = 'commission-edit-' . $commission->id;
                                                                    $commissionEditMsg      = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::COM,
                                                                        'commission_edit_route_unavailable'
                                                                    ) ?? 'Commission edit route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    <a
                                                                        id="{{ $commissionEditBtnId }}"
                                                                        href="{{ $commissionEditRoute }}"
                                                                        data-url="{{ $commissionEditRoute }}"
                                                                        data-guard-msg="{{ $commissionEditMsg }}"
                                                                        data-size="lg"
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Edit Commission') }}"
                                                                        class="{{ VC::BT_SM_CT }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        data-original-title="{{ __('Edit') }}"
                                                                    >
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $commissionEditBtnId }}');
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
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
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
                                                            @endcan
                                                            @can('delete commission')
                                                                @php
                                                                    $commissionDestroyRoute = Route::has(ViewsConstants::COM . '.destroy')
                                                                        ? route(ViewsConstants::COM . '.destroy', $commission->id)
                                                                        : '#';
                                                                    $commissionDeleteBtnId  = 'commission-delete-' . $commission->id;
                                                                    $commissionDeleteFormId = 'commission-delete-form-' . $commission->id;
                                                                    $commissionDestroyMsg   = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::COM,
                                                                        'commission_destroy_route_unavailable'
                                                                    ) ?? 'Commission destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    {!! Collective\Html\FormFacade::open([
                                                                        'route'  => [ViewsConstants::COM . '.destroy', $commission->id],
                                                                        'method' => 'DELETE',
                                                                        'id'     => $commissionDeleteFormId,
                                                                    ]) !!}
                                                                    <a id="{{ $commissionDeleteBtnId }}"
                                                                    href="#"
                                                                    data-url="{{ $commissionDestroyRoute }}"
                                                                    data-guard-msg="{{ $commissionDestroyMsg }}"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Delete') }}"
                                                                    data-original-title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('{{ $commissionDeleteFormId }}').submit();">
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                    {!! Collective\Html\FormFacade::close() !!}
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $commissionDeleteBtnId }}');
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
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
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
                                                            @endcan
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="mt-2 text-center">
                                        {{ __('No Commission Found!') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::CD }} min-height-253">
                        <div class="card-header">
                            <div class="{{ VC::RW }}">
                                <div class="col">
                                    <h6 class="mb-0">{{ __('Loan') }}</h6>
                                </div>
                                @can('create loan')
                                    @php
                                        $loanCreateRoute = Route::has(ViewsConstants::LN . '.create')
                                            ? route(ViewsConstants::LN . '.create', $employee->id)
                                            : '#';
                                        $loanCreateBtnId = 'loan-create-' . $employee->id;
                                        $loanCreateMsg   = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::LN,
                                            'loan_create_route_unavailable'
                                        ) ?? 'Loan create route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <div class="col {{ VC::JCE }}">
                                        <a
                                            id="{{ $loanCreateBtnId }}"
                                            href="{{ $loanCreateRoute }}"
                                            data-url="{{ $loanCreateRoute }}"
                                            data-guard-msg="{{ $loanCreateMsg }}"
                                            data-size="lg"
                                            data-ajax-popup="true"
                                            data-title="{{ __('Create Loan') }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Create') }}"
                                            data-original-title="{{ __('Create Loan') }}"
                                            class="{{ VC::BT_SM_PM }}"
                                        >
                                            <i class="{{ VC::TI_PLS }}"></i>
                                        </a>
                                    </div>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const btn = document.getElementById('{{ $loanCreateBtnId }}');
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
                                                            const toastEl = document.createElement('div');
                                                            toastEl.className = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
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
                                @endcan
                            </div>
                        </div>
                        <div class="card-body table-border-style full-card">
                            <div class="table-responsive">
                                @if(!$loans->isEmpty())
                                    <table class="{{ VC::TB }} table-striped {{ VC::MB0 }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee') }}</th>
                                                <th>{{ __('Loan Options') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Loan Amount') }}</th>
                                                @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($loans as $loan)
                                                <tr>
                                                    <td>{{ $employee->name }}</td>
                                                    <td>{{ $loan->loanOption?->name ?? '' }}</td>
                                                    <td>{{ $loan->title }}</td>
                                                    <td>{{ ucfirst($loan->type) }}</td>
                                                    @if($loan->type === 'fixed')
                                                        <td>{{ $user?->priceFormat($loan->amount) }}</td>
                                                    @else
                                                        <td>{{ $loan->amount }}% (${{ $loan->tota_allow }})</td>
                                                    @endif
                                                    @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                                        <td>
                                                            @can('edit loan')
                                                                @php
                                                                    $loanEditRoute = Route::has(ViewsConstants::LN . '.edit')
                                                                        ? route(ViewsConstants::LN . '.edit', $loan->id)
                                                                        : (Route::has(Str::kebab(ViewsConstants::LN . '.edit'))
                                                                            ? route(Str::kebab(ViewsConstants::LN . '.edit'), $loan->id)
                                                                            : '#');
                                                                
                                                                    $loanEditBtnId = 'loan-edit-' . $loan->id;
                                                                    $loanEditMsg   = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::LN,
                                                                        'loan_edit_route_unavailable'
                                                                    ) ?? 'Loan edit route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    <a id="{{ $loanEditBtnId }}"
                                                                    href="{{ $loanEditRoute }}"
                                                                    data-url="{{ $loanEditRoute }}"
                                                                    data-guard-msg="{{ $loanEditMsg }}"
                                                                    data-size="lg"
                                                                    data-ajax-popup="true"
                                                                    data-title="{{ __('Edit Loan') }}"
                                                                    class="{{ VC::BT_SM_CT }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Edit') }}"
                                                                    data-original-title="{{ __('Edit') }}">
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $loanEditBtnId }}');
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
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
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
                                                            @endcan
                                                            @can('delete loan')
                                                                @php
                                                                    $loanDestroyRoute  = Route::has(ViewsConstants::LN . '.destroy')
                                                                        ? route(ViewsConstants::LN . '.destroy', $loan->id)
                                                                        : (Route::has(Str::kebab(ViewsConstants::LN . '.destroy'))
                                                                            ? route(Str::kebab(ViewsConstants::LN . '.destroy'), $loan->id)
                                                                            : '#');
                                                                    $loanDeleteBtnId   = 'loan-delete-' . $loan->id;
                                                                    $loanDeleteFormId  = 'loan-delete-form-' . $loan->id;
                                                                    $loanDestroyMsg    = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::LN,
                                                                        'loan_destroy_route_unavailable'
                                                                    ) ?? 'Loan destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    {!! Collective\Html\FormFacade::open([
                                                                        'url'            => $loanDestroyRoute,
                                                                        'method'         => 'DELETE',
                                                                        'id'             => $loanDeleteFormId,
                                                                    ]) !!}
                                                                    <a id="{{ $loanDeleteBtnId }}"
                                                                    href="#"
                                                                    data-url="{{ $loanDestroyRoute }}"
                                                                    data-guard-msg="{{ $loanDestroyMsg }}"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Delete') }}"
                                                                    data-original-title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('{{ $loanDeleteFormId }}').submit();">
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                    {!! Collective\Html\FormFacade::close() !!}
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $loanDeleteBtnId }}');
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
                                                                                        const toastEl     = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body        = document.createElement('div');
                                                                                        body.className    = 'toast-body';
                                                                                        body.textContent  = msg;
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
                                                            @endcan
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="mt-2 text-center">
                                        {{ __('No Loan Data Found!') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::CD }} min-height-253">
                        <div class="card-header">
                            <div class="{{ VC::RW }}">
                                <div class="col">
                                    <h6 class="mb-0">{{ __('Loan') }}</h6>
                                </div>
                                @can('create loan')
                                    <div class="col {{ VC::JCE }}">
                                        @php
                                            $loanCreateRoute    = Route::has(ViewsConstants::LN . '.create')
                                                ? route(ViewsConstants::LN . '.create', $employee->id)
                                                : '#';
                                            $loanCreateBtnId    = 'loan-create-' . $employee->id;
                                            $loanCreateMsg      = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::LN,
                                                'loan_create_route_unavailable'
                                            ) ?? 'Loan create route is unavailable. Please contact technical support or your domain administrator.';
                                        @endphp
                                        <div class="col {{ VC::JCE }}">
                                            <a
                                                id="{{ $loanCreateBtnId }}"
                                                href="{{ $loanCreateRoute }}"
                                                data-url="{{ $loanCreateRoute }}"
                                                data-guard-msg="{{ $loanCreateMsg }}"
                                                data-size="lg"
                                                data-ajax-popup="true"
                                                data-title="{{ __('Create Loan') }}"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Create') }}"
                                                data-original-title="{{ __('Create Loan') }}"
                                                class="{{ VC::BT_SM_PM }}"
                                            >
                                                <i class="{{ VC::TI_PLS }}"></i>
                                            </a>
                                        </div>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const btn = document.getElementById('{{ $loanCreateBtnId }}');
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
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
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
                            </div>
                        </div>
                        <div class="card-body table-border-style full-card">
                            <div class="table-responsive">
                                @if(!$loans->isEmpty())
                                    <table class="{{ VC::TB }} table-striped {{ VC::MB0 }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee') }}</th>
                                                <th>{{ __('Loan Options') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Loan Amount') }}</th>
                                                @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($loans as $loan)
                                                <tr>
                                                    <td>{{ $employee->name }}</td>
                                                    <td>{{ $loan->loanOption?->name ?? '' }}</td>
                                                    <td>{{ $loan->title }}</td>
                                                    <td>{{ ucfirst($loan->type) }}</td>
                                                    @if($loan->type === 'fixed')
                                                        <td>{{ $user?->priceFormat($loan->amount) }}</td>
                                                    @else
                                                        <td>{{ $loan->amount }}% (${{ $loan->tota_allow }})</td>
                                                    @endif

                                                    @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                                        <td>
                                                            @can('edit loan')
                                                                @php
                                                                    $loanEditRoute      = Route::has(ViewsConstants::LN . '.edit')
                                                                        ? route(ViewsConstants::LN . '.edit', $loan->id)
                                                                        : '#';
                                                                    $loanEditBtnId      = 'loan-edit-' . $loan->id;
                                                                    $loanEditMsg        = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::LN,
                                                                        'loan_edit_route_unavailable'
                                                                    ) ?? 'Loan edit route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    <a
                                                                        id="{{ $loanEditBtnId }}"
                                                                        href="{{ $loanEditRoute }}"
                                                                        data-url="{{ $loanEditRoute }}"
                                                                        data-guard-msg="{{ $loanEditMsg }}"
                                                                        data-size="lg"
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Edit Loan') }}"
                                                                        class="{{ VC::BT_SM_CT }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        data-original-title="{{ __('Edit') }}"
                                                                    >
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $loanEditBtnId }}');
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
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
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
                                                            @endcan
                                                            @can('delete loan')
                                                                @php
                                                                    $loanDeleteRoute    = Route::has(ViewsConstants::LN . '.destroy')
                                                                        ? route(ViewsConstants::LN . '.destroy', $loan->id)
                                                                        : (Route::has(Str::kebab(ViewsConstants::LN . '.destroy'))
                                                                            ? route(Str::kebab(ViewsConstants::LN . '.destroy'), $loan->id)
                                                                            : '#');
                                                                    $loanDeleteBtnId    = 'loan-delete-' . $loan->id;
                                                                    $loanDeleteFormId   = 'loan-delete-form-' . $loan->id;
                                                                    $loanDeleteMsg      = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::LN,
                                                                        'loan_destroy_route_unavailable'
                                                                    ) ?? 'Loan destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    {!! Collective\Html\FormFacade::open([
                                                                        'url'            => $loanDeleteRoute,
                                                                        'method'         => 'DELETE',
                                                                        'id'             => $loanDeleteFormId,
                                                                        'data-url'       => $loanDeleteRoute,
                                                                        'data-guard-msg' => $loanDeleteMsg,
                                                                    ]) !!}
                                                                    <a
                                                                        id="{{ $loanDeleteBtnId }}"
                                                                        href="#"
                                                                        data-url="{{ $loanDeleteRoute }}"
                                                                        data-guard-msg="{{ $loanDeleteMsg }}"
                                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}"
                                                                        data-original-title="{{ __('Delete') }}"
                                                                        data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="document.getElementById('{{ $loanDeleteFormId }}').submit();"
                                                                    >
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                    {!! Collective\Html\FormFacade::close() !!}
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $loanDeleteBtnId }}');
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
                                                                                        container = document.createElement('div');
                                                                                        container.id = 'toast-container';
                                                                                        document.body.appendChild(container);
                                                                                    }
                                                                                    if (bootstrapLink && window.bootstrap) {
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
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
                                                            @endcan
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="mt-2 text-center">
                                        {{ __('No Loan Data Found!') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::CD }} min-height-253">
                        <div class="card-header">
                            <div class="{{ VC::RW }}">
                                <div class="col">
                                    <h6 class="mb-0">{{ __('Saturation Deduction') }}</h6>
                                </div>
                                @can('create saturation deduction')
                                    @php
                                        $sdCreateRoute = Route::has(ViewsConstants::STR_DD . '.create')
                                            ? route(ViewsConstants::STR_DD . '.create', $employee->id)
                                            : (Route::has(Str::kebab(ViewsConstants::STR_DD . '.create'))
                                                ? route(Str::kebab(ViewsConstants::STR_DD . '.create'), $employee->id)
                                                : '#');
                                        $sdCreateBtnId = 'saturation-deduction-create-' . $employee->id;
                                        $sdCreateMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::STR_DD,
                                            'saturation_deduction_create_route_unavailable'
                                        ) ?? 'Saturation deduction create route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <div class="col {{ VC::JCE }}">
                                        <a
                                            id="{{ $sdCreateBtnId }}"
                                            href="{{ $sdCreateRoute }}"
                                            data-url="{{ $sdCreateRoute }}"
                                            data-guard-msg="{{ $sdCreateMsg }}"
                                            data-size="lg"
                                            data-ajax-popup="true"
                                            data-title="{{ __('Create Saturation Deduction') }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Create') }}"
                                            data-original-title="{{ __('Create Saturation Deduction') }}"
                                            class="{{ VC::BT_SM_PM }}"
                                        >
                                            <i class="{{ VC::TI_PLS }}"></i>
                                        </a>
                                    </div>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const btn = document.getElementById('{{ $sdCreateBtnId }}');
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
                                                            container    = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (bootstrapLink && window.bootstrap) {
                                                            const toastEl = document.createElement('div');
                                                            toastEl.className = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className  = 'toast-body';
                                                            body.textContent = msg;
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
                                @endcan
                            </div>
                        </div>
                        <div class="card-body table-border-style full-card">
                            <div class="table-responsive">
                                @if(!$saturationdeductions->isEmpty())
                                    <table class="{{ VC::TB }} table-striped {{ VC::MB0 }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee Name') }}</th>
                                                <th>{{ __('Deduction Option') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($saturationdeductions as $deduction)
                                                <tr>
                                                    <td>{{ $employee[UsersConstants::COL_NM] }}</td>
                                                    <td>{{ $deduction->deductionOption?->name ?? '' }}</td>
                                                    <td>{{ $deduction->title }}</td>
                                                    <td>{{ ucfirst($deduction->type) }}</td>
                                                    @if($deduction->type === 'fixed')
                                                        <td>{{ $user?->priceFormat($deduction->amount) }}</td>
                                                    @else
                                                        <td>{{ $deduction->amount }}% (${{ $deduction->tota_allow }})</td>
                                                    @endif
                                                    @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                                        <td>
                                                            @can('edit saturation deduction')
                                                                @php
                                                                    $sdEditRoute     = Route::has(ViewsConstants::STR_DD . '.edit')
                                                                        ? route(ViewsConstants::STR_DD . '.edit', $deduction->id)
                                                                        : (Route::has(Str::kebab(ViewsConstants::STR_DD . '.edit'))
                                                                            ? route(Str::kebab(ViewsConstants::STR_DD . '.edit'), $deduction->id)
                                                                            : '#');
                                                                    $sdEditBtnId     = 'saturation-deduction-edit-' . $deduction->id;
                                                                    $sdEditMsg       = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::STR_DD,
                                                                        'saturation_deduction_edit_route_unavailable'
                                                                    ) ?? 'Saturation deduction edit route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    <a id="{{ $sdEditBtnId }}"
                                                                    href="{{ $sdEditRoute }}"
                                                                    data-url="{{ $sdEditRoute }}"
                                                                    data-guard-msg="{{ $sdEditMsg }}"
                                                                    data-size="lg"
                                                                    data-ajax-popup="true"
                                                                    data-title="{{ __('Edit Saturation Deduction') }}"
                                                                    class="{{ VC::BT_SM_CT }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Edit') }}"
                                                                    data-original-title="{{ __('Edit') }}">
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $sdEditBtnId }}');
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
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
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
                                                            @endcan
                                                            @can('delete saturation deduction')
                                                                @php
                                                                    $sdDestroyRoute      = Route::has(ViewsConstants::STR_DD . '.destroy')
                                                                        ? route(ViewsConstants::STR_DD . '.destroy', $deduction->id)
                                                                        : (Route::has(Str::kebab(ViewsConstants::STR_DD . '.destroy'))
                                                                            ? route(Str::kebab(ViewsConstants::STR_DD . '.destroy'), $deduction->id)
                                                                            : '#');
                                                                    $sdDeleteBtnId       = 'deduction-delete-' . $deduction->id;
                                                                    $sdDeleteFormId      = 'deduction-delete-form-' . $deduction->id;
                                                                    $sdDestroyMsg        = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::STR_DD,
                                                                        'saturation_deduction_destroy_route_unavailable'
                                                                    ) ?? 'Saturation deduction destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    {!! Collective\Html\FormFacade::open([
                                                                        'url'            => $sdDestroyRoute,
                                                                        'method'         => 'DELETE',
                                                                        'id'             => $sdDeleteFormId,
                                                                        'data-url'       => $sdDestroyRoute,
                                                                        'data-guard-msg' => $sdDestroyMsg,
                                                                    ]) !!}
                                                                    <a
                                                                        id="{{ $sdDeleteBtnId }}"
                                                                        href="#"
                                                                        data-url="{{ $sdDestroyRoute }}"
                                                                        data-guard-msg="{{ $sdDestroyMsg }}"
                                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}"
                                                                        data-original-title="{{ __('Delete') }}"
                                                                        data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="document.getElementById('{{ $sdDeleteFormId }}').submit();"
                                                                    >
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                    {!! Collective\Html\FormFacade::close() !!}
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $sdDeleteBtnId }}');
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
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
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
                                                            @endcan
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="mt-2 text-center">
                                        {{ __('No Saturation Deduction Found!') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::CD }} min-height-253">
                        <div class="card-header">
                            <div class="{{ VC::RW }}">
                                <div class="col">
                                    <h6 class="mb-0">{{ __('Other Payment') }}</h6>
                                </div>
                                @can('create other payment')
                                    @php
                                        $otherPaymentCreateRoute  = Route::has(ViewsConstants::OT_PAY . '.create')
                                            ? route(ViewsConstants::OT_PAY . '.create', $employee->id)
                                            : (Route::has(Str::kebab(ViewsConstants::OT_PAY . '.create'))
                                                ? route(Str::kebab(ViewsConstants::OT_PAY . '.create'), $employee->id)
                                                : '#');
                                        $otherPaymentCreateBtnId  = 'other-payment-create-' . $employee->id;
                                        $otherPaymentCreateMsg    = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::OT_PAY,
                                            'other_payment_create_route_unavailable'
                                        ) ?? 'Other payment create route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <a
                                        id="{{ $otherPaymentCreateBtnId }}"
                                        href="{{ $otherPaymentCreateRoute }}"
                                        data-url="{{ $otherPaymentCreateRoute }}"
                                        data-guard-msg="{{ $otherPaymentCreateMsg }}"
                                        data-size="lg"
                                        data-ajax-popup="true"
                                        data-title="{{ __('Create Other Payment') }}"
                                        data-bs-toggle="tooltip"
                                        title="{{ __('Create') }}"
                                        class="{{ VC::BT_SM_PM }}"
                                    >
                                        <i class="{{ VC::TI_PLS }}"></i>
                                    </a>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const btn = document.getElementById('{{ $otherPaymentCreateBtnId }}');
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
                                                            const toastEl = document.createElement('div');
                                                            toastEl.className = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
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
                                @endcan
                            </div>
                        </div>
                        <div class="card-body table-border-style full-card">
                            <div class="table-responsive">
                                @if(!$otherpayments->isEmpty())
                                    <table class="{{ VC::TB }} table-striped {{ VC::MB0 }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($otherpayments as $p)
                                                <tr>
                                                    <td>{{ $employee->name }}</td>
                                                    <td>{{ $p->title }}</td>
                                                    <td>{{ ucfirst($p->type) }}</td>
                                                    @if($p->type === 'fixed')
                                                        <td>{{ $user?->priceFormat($p->amount) }}</td>
                                                    @else
                                                        <td>{{ $p->amount }}% (${{ $p->tota_allow }})</td>
                                                    @endif
                                                    @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                                        <td>
                                                            @can('edit other payment')
                                                                @php
                                                                    $otherPaymentEditRoute   = Route::has(ViewsConstants::OT_PAY . '.edit')
                                                                        ? route(ViewsConstants::OT_PAY . '.edit', $p->id)
                                                                        : (Route::has(Str::kebab(ViewsConstants::OT_PAY . '.edit'))
                                                                            ? route(Str::kebab(ViewsConstants::OT_PAY . '.edit'), $p->id)
                                                                            : '#');
                                                                    $otherPaymentEditBtnId   = 'other-payment-edit-' . $p->id;
                                                                    $otherPaymentEditMsg     = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::OT_PAY,
                                                                        'other_payment_edit_route_unavailable'
                                                                    ) ?? 'Other payment edit route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    <a
                                                                        id="{{ $otherPaymentEditBtnId }}"
                                                                        href="{{ $otherPaymentEditRoute }}"
                                                                        data-url="{{ $otherPaymentEditRoute }}"
                                                                        data-guard-msg="{{ $otherPaymentEditMsg }}"
                                                                        data-size="lg"
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Edit Other Payment') }}"
                                                                        class="{{ VC::BT_SM_CT }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        data-original-title="{{ __('Edit') }}"
                                                                    >
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $otherPaymentEditBtnId }}');
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
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
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
                                                            @endcan
                                                            @can('delete other payment')
                                                                @php
                                                                    use Illuminate\Support\Facades\Route;
                                                                    use Illuminate\Support\Str;
                                                                    use App\Config\Constants\{ViewsConstants, StacksConstants};
                                                                    use App\Models\Utility;
                                                                
                                                                    $paymentDeleteRoute   = Route::has(ViewsConstants::OT_PAY . '.destroy')
                                                                        ? route(ViewsConstants::OT_PAY . '.destroy', $p->id)
                                                                        : '#';
                                                                    $paymentDeleteBtnId   = 'payment-delete-' . $p->id;
                                                                    $paymentDeleteFormId  = 'payment-delete-form-' . $p->id;
                                                                    $paymentDeleteMsg     = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::OT_PAY,
                                                                        'other_payment_destroy_route_unavailable'
                                                                    ) ?? 'Other payment destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    {!! Collective\Html\FormFacade::open([
                                                                        'url'            => $paymentDeleteRoute,
                                                                        'method'         => 'DELETE',
                                                                        'id'             => $paymentDeleteFormId,
                                                                        'data-url'       => $paymentDeleteRoute,
                                                                        'data-guard-msg' => $paymentDeleteMsg,
                                                                    ]) !!}
                                                                    <a
                                                                        id="{{ $paymentDeleteBtnId }}"
                                                                        href="#"
                                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}"
                                                                        data-original-title="{{ __('Delete') }}"
                                                                        data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="document.getElementById('{{ $paymentDeleteFormId }}').submit();"
                                                                    >
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                    {!! Collective\Html\FormFacade::close() !!}
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $paymentDeleteBtnId }}');
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
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
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
                                                            @endcan
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="mt-2 text-center">{{ __('No Other Payment Data Found!') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::CD }} min-height-253">
                        <div class="card-header">
                            <div class="{{ VC::RW }}">
                                <div class="col">
                                    <h6 class="mb-0">{{ __('Overtime') }}</h6>
                                </div>
                                    @php
                                        $overtimeCreateRoute     = Route::has(ViewsConstants::OVT . '.create')
                                            ? route(ViewsConstants::OVT . '.create', $employee->id)
                                            : '#';
                                        $overtimeCreateBtnId     = 'overtime-create-' . $employee->id;
                                        $overtimeCreateMsg       = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::OVT,
                                            'overtime_create_route_unavailable'
                                        ) ?? 'Overtime create route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <div class="col {{ VC::JCE }}">
                                        <a
                                            id="{{ $overtimeCreateBtnId }}"
                                            href="{{ $overtimeCreateRoute }}"
                                            data-url="{{ $overtimeCreateRoute }}"
                                            data-guard-msg="{{ $overtimeCreateMsg }}"
                                            data-size="md"
                                            data-ajax-popup="true"
                                            data-title="{{ __('Create Overtime') }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Create') }}"
                                            class="{{ VC::BT_SM_PM }}"
                                        >
                                            <i class="{{ VC::TI_PLS }}"></i>
                                        </a>
                                    </div>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const btn = document.getElementById('{{ $overtimeCreateBtnId }}');
                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                btn.setAttribute('data-listener-active', 'true');
                                                btn.addEventListener('click', event => {
                                                    try {
                                                        const href = btn.getAttribute('href');
                                                        const url = btn.getAttribute('data-url');
                                                        if ((href && href !== '#') || (url && url !== '#')) return;
                                                        event.preventDefault();
                                                        const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                        let container = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (bootstrapLink && window.bootstrap) {
                                                            const toastEl = document.createElement('div');
                                                            toastEl.className = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
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
                                @endcan
                            </div>
                        </div>
                        <div class="card-body table-border-style full-card">
                            <div class="table-responsive">
                                @if(!$overtimes->isEmpty())
                                    <table class="{{ VC::TB }} table-striped {{ VC::MB0 }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee Name') }}</th>
                                                <th>{{ __('Overtime Title') }}</th>
                                                <th>{{ __('Number of days') }}</th>
                                                <th>{{ __('Hours') }}</th>
                                                <th>{{ __('Rate') }}</th>
                                                @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($overtimes as $o)
                                                <tr>
                                                    <td>{{ $employee->name }}</td>
                                                    <td>{{ $o->title }}</td>
                                                    <td>{{ $o->number_of_days }}</td>
                                                    <td>{{ $o->hours }}</td>
                                                    <td>{{ $user?->priceFormat($o->rate) }}</td>

                                                    @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                                        <td>
                                                            @can('edit overtime')
                                                                @php
                                                                    $overtimeEditRoute    = Route::has(ViewsConstants::OVT . '.edit')
                                                                        ? route(ViewsConstants::OVT . '.edit', $o->id)
                                                                        : '#';
                                                                    $overtimeEditBtnId    = 'overtime-edit-' . $o->id;
                                                                    $overtimeEditMsg      = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::OVT,
                                                                        'overtime_edit_route_unavailable'
                                                                    ) ?? 'Overtime edit route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    <a
                                                                        id="{{ $overtimeEditBtnId }}"
                                                                        href="{{ $overtimeEditRoute }}"
                                                                        data-url="{{ $overtimeEditRoute }}"
                                                                        data-guard-msg="{{ $overtimeEditMsg }}"
                                                                        data-size="lg"
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Edit Overtime') }}"
                                                                        class="{{ VC::BT_SM_CT }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                    >
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $overtimeEditBtnId }}');
                                                                            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                            btn.setAttribute('data-listener-active', 'true');
                                                                            btn.addEventListener('click', event => {
                                                                                try {
                                                                                    const href          = btn.getAttribute('href');
                                                                                    const url           = btn.getAttribute('data-url');
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
                                                                                        const toastEl    = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body       = document.createElement('div');
                                                                                        body.className   = 'toast-body';
                                                                                        body.textContent = msg;
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
                                                            @endcan
                                                            @can('delete overtime')
                                                                @php
                                                                    $overtimeDestroyRoute = Route::has(ViewsConstants::OVT . '.destroy')
                                                                        ? route(ViewsConstants::OVT . '.destroy', $o->id)
                                                                        : (Route::has(Str::kebab(ViewsConstants::OVT . '.destroy'))
                                                                            ? route(Str::kebab(ViewsConstants::OVT . '.destroy'), $o->id)
                                                                            : '#');
                                                                    $overtimeDeleteFormId = 'overtime-delete-form-' . $o->id;
                                                                    $overtimeDeleteBtnId  = 'overtime-delete-' . $o->id;
                                                                    $overtimeDestroyMsg   = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::OVT,
                                                                        'overtime_destroy_route_unavailable'
                                                                    ) ?? 'Overtime destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    {!! Collective\Html\FormFacade::open([
                                                                        'url'            => $overtimeDestroyRoute,
                                                                        'method'         => 'DELETE',
                                                                        'id'             => $overtimeDeleteFormId,
                                                                        'data-url'       => $overtimeDestroyRoute,
                                                                        'data-guard-msg' => $overtimeDestroyMsg,
                                                                    ]) !!}
                                                                    <a
                                                                        id="{{ $overtimeDeleteBtnId }}"
                                                                        href="#"
                                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}"
                                                                        data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="document.getElementById('{{ $overtimeDeleteFormId }}').submit();"
                                                                    >
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                    {!! Collective\Html\FormFacade::close() !!}
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $overtimeDeleteBtnId }}');
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
                                                            @endcan
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="mt-2 text-center">{{ __('No Overtime Data Found!') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@push(StacksConstants::ADM_SCR_PG)
    @if(!empty($employee))
            <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
                ar: {
                    amount: 'المبلغ',
                    percentage: 'النسبة المئوية',
                    select_any_designation: 'اختر أي مسمى وظيفي'
                },
                da: {
                    amount: 'Beløb',
                    percentage: 'Procent',
                    select_any_designation: 'Vælg en stillingsbetegnelse'
                },
                de: {
                    amount: 'Betrag',
                    percentage: 'Prozentsatz',
                    select_any_designation: 'Beliebige Position auswählen'
                },
                en: {
                    amount: 'Amount',
                    percentage: 'Percentage',
                    select_any_designation: 'Select any Designation'
                },
                es: {
                    amount: 'Cantidad',
                    percentage: 'Porcentaje',
                    select_any_designation: 'Selecciona una designación'
                },
                fr: {
                    amount: 'Montant',
                    percentage: 'Pourcentage',
                    select_any_designation: 'Sélectionnez une désignation'
                },
                he: {
                    amount: 'סכום',
                    percentage: 'אחוז',
                    select_any_designation: 'בחר כל מינוי'
                },
                it: {
                    amount: 'Importo',
                    percentage: 'Percentuale',
                    select_any_designation: 'Seleziona una qualifica'
                },
                ja: {
                    amount: '金額',
                    percentage: 'パーセンテージ',
                    select_any_designation: '任意の役職を選択'
                },
                nl: {
                    amount: 'Bedrag',
                    percentage: 'Percentage',
                    select_any_designation: 'Selecteer een functie'
                },
                pl: {
                    amount: 'Kwota',
                    percentage: 'Procent',
                    select_any_designation: 'Wybierz dowolne stanowisko'
                },
                pt: {
                    amount: 'Valor',
                    percentage: 'Percentagem',
                    select_any_designation: 'Selecione uma Designação'
                },
                'pt-br': {
                    amount: 'Valor',
                    percentage: 'Percentual',
                    select_any_designation: 'Selecione uma designação'
                },
                ru: {
                    amount: 'Сумма',
                    percentage: 'Процент',
                    select_any_designation: 'Выберите должность'
                },
                tr: {
                    amount: 'Tutar',
                    percentage: 'Yüzde',
                    select_any_designation: 'Herhangi bir pozisyon seçin'
                },
                zh: {
                    amount: '金额',
                    percentage: '百分比',
                    select_any_designation: '选择任意职称'
                }
            };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
         
          })();
    </script>
        <script defer>
            (() => {
            const errFb = '# ERROR';
            const dataClientLocalized = 'data-client-localized';
            const dataGuardMsg = 'data-guard-msg';
            const langSessionKey = 'erp-np-lang';

            function getLocalizedMessage(msgKey, el) {
                let msg = errFb;
                if (el.getAttribute('data-sv-localized') === 'true' ||
                    el.getAttribute(dataClientLocalized) === 'true') {
                msg = el.getAttribute(dataGuardMsg) ?? errFb;
                } else {
                let lang = (
                    window.sessionStorage.getItem(langSessionKey) ??
                    document.documentElement.lang ??
                    'en'
                ).toLowerCase().replace(/_/g, '-');
                lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                msg = window.translations?.[lang]?.[msgKey] ??
                        el.getAttribute(dataGuardMsg) ??
                        window.translations?.['en']?.[msgKey] ??
                        errFb;
                if (msg !== errFb) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, 'true');
                }
                }
                return msg;
            }

            function showError(message) {
                try {
                let container = document.getElementById('toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'toast-container';
                    document.body.appendChild(container);
                }
                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                if (bootstrapLink && window.bootstrap) {
                    const toastEl = document.createElement('div');
                    toastEl.className = 'toast';
                    toastEl.setAttribute('role', 'alert');
                    toastEl.setAttribute('aria-live', 'assertive');
                    toastEl.setAttribute('aria-atomic', 'true');
                    const body = document.createElement('div');
                    body.className = 'toast-body';
                    body.textContent = message;
                    toastEl.appendChild(body);
                    container.appendChild(toastEl);
                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                } else {
                    alert(message);
                }
                } catch {
                alert(message);
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                // Amount type toggle
                document.querySelectorAll('.amount_type').forEach(el => {
                if (el.dataset.listenerAttached === 'true') return;
                el.dataset.listenerAttached = 'true';
                const onChange = () => {
                    try {
                    const key = el.value === 'percentage' ? 'percentage' : 'amount';
                    document.querySelectorAll('.amount_label').forEach(lbl => {
                        lbl.textContent = getLocalizedMessage(key, lbl);
                    });
                    } catch {
                    showError(getLocalizedMessage('amount', el));
                    }
                };
                el.addEventListener('change', onChange);
                });

                // Populate designations and init tables
                const deptEl = document.getElementById('department_id');
                const designationId = '{{ $employee->designation_id }}';
                if (deptEl) {
                const load = () => loadDesignations(deptEl.value);
                deptEl.dataset.listenerAttached || (() => {
                    deptEl.dataset.listenerAttached = 'true';
                    deptEl.addEventListener('change', load);
                })();
                load();
                }

                ['allowance','commission','loan','saturation-deduction','other-payment','overtime']
                .forEach(pref => {
                    const tbl = document.getElementById(`${pref}-dataTable`);
                    try {
                    tbl && $(`#${pref}-dataTable`).dataTable({
                        columnDefs: [{ sortable: false, targets: [1] }]
                    });
                    } catch {
                    showError(getLocalizedMessage('amount', tbl || document.body));
                    }
                });
            });

            function loadDesignations(deptId) {
                const el = document.getElementById('designation_id');
                if (!el) return;
                $.ajax({
                url: '{{ route(ViewsConstants::EMP.".json") }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    department_id: deptId,
                    _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
                }
                })
                .done(data => {
                    el.innerHTML = '';
                    const opt0 = document.createElement('option');
                    opt0.value = '';
                    opt0.textContent = getLocalizedMessage('select_any_designation', el);
                    el.appendChild(opt0);
                    Object.entries(data).forEach(([key, val]) => {
                    const o = document.createElement('option');
                    o.value = key;
                    if (key === designationId) o.selected = true;
                    o.textContent = val;
                    el.appendChild(o);
                    });
                })
                .fail(() => {
                    showError(getLocalizedMessage('select_any_designation', el));
                });
            }
            })();
        </script>
    @endif
@endpush
