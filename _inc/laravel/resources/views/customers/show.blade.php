@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Invoice, Proposal, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Customer-Detail')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        $indexRoute = Route::has(ViewsConstants::CST . '.index')
            ? route(ViewsConstants::CST . '.index')
            : '#';
        $indexGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CST,
            'customers_index_route_unavailable'
        ) ?? 'Customer index route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <li class="breadcrumb-item">
        <a
            id="customer-index-breadcrumb"
            href="{{ $indexRoute }}"
            data-url="{{ $indexRoute }}"
            data-guard-msg="{{ $indexGuardMsg }}"
        >
            {{ __('Customer') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/customers/showIndex.js') }}"></script>
    @endpush
    <li class="breadcrumb-item">{{!empty($customer['name']) ? $customer['name'] : 'Undefined Customer' }}</li>
@endsection
@if(!empty($customer) && isset($customer->id))
@else
    <div class="text-muted">{{ __('Customer not found') }}</div>
@endif
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/customers/lang/url.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/customers/url.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create invoice')
            @php
                $invoiceRoute = Route::has(ViewsConstants::INV . '.create')
                    ? route(ViewsConstants::INV . '.create', $customer->id)
                    : '#';
                $invoiceGuardMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::INV,
                    'invoice_create_route_unavailable'
                ) ?? 'Invoice create route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                id="invoice-create-btn-{{ $customer->id }}"
                href="{{ $invoiceRoute }}"
                data-url="{{ $invoiceRoute }}"
                data-guard-msg="{{ $invoiceGuardMsg }}"
                class="{{ VC::BT_SM_PM }}"
            >
                {{ __('Create Invoice') }}
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const btn = document.getElementById('invoice-create-btn-{{ $customer->id }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active', 'true');
                        btn.addEventListener('click', e => {
                            try {
                                const url = btn.getAttribute('data-url') ?? '#';
                                if (url !== '#') return;
                                e.preventDefault();
                                const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bs) {
                                    const toast = document.createElement('div');
                                    toast.className = 'toast';
                                    toast.setAttribute('role','alert');
                                    toast.setAttribute('aria-live','assertive');
                                    toast.setAttribute('aria-atomic','true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toast.appendChild(body);
                                    container.appendChild(toast);
                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                } else {
                                    alert(msg);
                                }
                                btn.setAttribute('data-failed-route','true');
                            } catch {}
                        });
                    })();
                </script>
            @endpush
        @endcan
        @can('create proposal')
            @php
                $createProposalRoute = Route::has(ViewsConstants::PPS . '.create')
                    ? route(ViewsConstants::PPS . '.create', $customer->id)
                    : '#';
                $createProposalGuardMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::PPS,
                    'create_proposal_route_unavailable'
                ) ?? 'Create proposal route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                id="create-proposal-btn-{{ $customer->id }}"
                href="{{ $createProposalRoute }}"
                data-url="{{ $createProposalRoute }}"
                data-guard-msg="{{ $createProposalGuardMsg }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create Proposal') }}"
            >
                {{ __('Create Proposal') }}
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const btn = document.getElementById('create-proposal-btn-{{ $customer->id }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active', 'true');
                        btn.addEventListener('click', e => {
                            try {
                                const url = btn.getAttribute('data-url') || '#';
                                if (url !== '#') return;
                                e.preventDefault();
                                const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bs) {
                                    const toast = document.createElement('div');
                                    toast.className = 'toast';
                                    toast.setAttribute('role','alert');
                                    toast.setAttribute('aria-live','assertive');
                                    toast.setAttribute('aria-atomic','true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toast.appendChild(body);
                                    container.appendChild(toast);
                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                } else {
                                    alert(msg);
                                }
                                btn.setAttribute('data-failed-route', 'true');
                            } catch (error) {}
                        });
                    })();
                </script>
            @endpush
        @endcan
        @can('edit customer')
        @php
            $editRoute = Route::has(ViewsConstants::CST . '.edit')
                ? route(ViewsConstants::CST . '.edit', $customer['id'])
                : '#';
            $editGuardMsg = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::CST,
                'customers_edit_route_unavailable'
            ) ?? 'Customer edit route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a
            id="customer-edit-btn-{{ $customer['id'] }}"
            href="{{ $editRoute }}"
            data-url="{{ $editRoute }}"
            data-guard-msg="{{ $editGuardMsg }}"
            data-size="lg"
            data-ajax-popup="true"
            class="{{ VC::BT_SM_PM }}"
            data-bs-toggle="tooltip"
            title="{{ __('Edit Customer') }}"
            data-title="{{ __('Edit Customer') }}"
        >
            <i class="{{ VC::TI_PC }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const btn = document.getElementById('customer-edit-btn-{{ $customer['id'] }}');
                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                    btn.setAttribute('data-listener-active', 'true');
                    btn.addEventListener('click', e => {
                        try {
                            const url = btn.getAttribute('data-url') ?? '#';
                            if (url !== '#') return;
                            e.preventDefault();
                            const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                            const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                            let container = document.getElementById('toast-container');
                            if (!container) {
                                container = document.createElement('div');
                                container.id = 'toast-container';
                                document.body.appendChild(container);
                            }
                            if (bs) {
                                const toast = document.createElement('div');
                                toast.className = 'toast';
                                toast.setAttribute('role','alert');
                                toast.setAttribute('aria-live','assertive');
                                toast.setAttribute('aria-atomic','true');
                                const body = document.createElement('div');
                                body.className = 'toast-body';
                                body.textContent = msg;
                                toast.appendChild(body);
                                container.appendChild(toast);
                                bootstrap.Toast.getOrCreateInstance(toast).show();
                            } else {
                                alert(msg);
                            }
                            btn.setAttribute('data-failed-route','true');
                        } catch {}
                    });
                })();
            </script>
        @endpush
        @endcan
        @can('delete customer')
            @php
                $deleteRoute = Route::has(ViewsConstants::CST . '.destroy')
                    ? route(ViewsConstants::CST . '.destroy', $customer['id'])
                    : (Route::has(Str::kebab(ViewsConstants::CST . '.destroy'))
                        ? route(Str::kebab(ViewsConstants::CST . '.destroy', $customer['id']))
                        : '#');
                $deleteGuardMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::CST,
                    'customers_destroy_route_unavailable'
                ) ?? 'Delete customer route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            {!! Form::open([
                'route'    => $deleteRoute,
                'method' => 'DELETE',
                'id'     => 'delete-form-' . $customer['id'],
                'class'  => 'delete-form-btn'
            ]) !!}
                <a
                    id="delete-customer-btn-{{ $customer['id'] }}"
                    href="{{ $deleteRoute }}"
                    data-url="{{ $deleteRoute }}"
                    data-guard-msg="{{ $deleteGuardMsg }}"
                    class="{{ VC::BT_SM_CT_PR }}"
                    data-bs-toggle="tooltip"
                    title="{{ __('Delete Customer') }}"
                    data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                    data-confirm-yes="document.getElementById('delete-form-{{ $customer['id'] }}').submit();"
                >
                    <i class="{{ VC::TI_TRS_WT }}"></i>
                </a>
            {!! Form::close() !!}
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const btn = document.getElementById('delete-customer-btn-{{ $customer['id'] }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active', 'true');
                        btn.addEventListener('click', e => {
                            try {
                                const url = btn.getAttribute('data-url') || '#';
                                if (url !== '#') return;
                                e.preventDefault();
                                const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bs) {
                                    const toast = document.createElement('div');
                                    toast.className = 'toast';
                                    toast.setAttribute('role', 'alert');
                                    toast.setAttribute('aria-live', 'assertive');
                                    toast.setAttribute('aria-atomic', 'true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toast.appendChild(body);
                                    container.appendChild(toast);
                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                } else {
                                    alert(msg);
                                }
                                btn.setAttribute('data-failed-route', 'true');
                            } catch (error) {}
                        });
                    })();
                </script>
            @endpush
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        @php
            $secList = ((is_array($customerInfoSections ?? null) && count($customerInfoSections)) || ($customerInfoSections instanceof Collection && $customerInfoSections->isNotEmpty())) ? $customerInfoSections : [];
        @endphp
        @foreach($secList as $section)
            @php
                $title  = isset($section['title']) && $section['title'] !== '' ? __($section['title']) : __('No section title available');
                $fields = Utility::isFilled($section['fields'] ?? []) ? $section['fields'] : [];
            @endphp
            <div class="{{ VC::CL4 }} {{ VC::MB4 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <h5 class="card-title">{{ $title }}</h5>
                        @forelse($fields as $field)
                            <p class="{{ VC::MB0 }}">{{ (isset($field) && $field !== '') ? $field : __('No value available') }}</p>
                        @empty
                            <p class="{{ VC::MB0 }}">{{ __('No details available') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }} pb-0">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Company Info') }}</h5>
                    <div class="{{ VC::RW }}">
                        @php
                            $stats = ((is_array($companyInfoStats ?? null) && count($companyInfoStats)) || ($companyInfoStats instanceof Collection && $companyInfoStats->isNotEmpty())) ? $companyInfoStats : [];
                        @endphp
                        @foreach($stats as $stat)
                            @php
                                $label       = isset($stat['label']) && $stat['label'] !== '' ? __($stat['label']) : __('No label available');
                                $value       = isset($stat['value']) && $stat['value'] !== '' ? $stat['value'] : __('No value available');
                                $secondLabel = isset($stat['secondLabel']) && $stat['secondLabel'] !== '' ? __($stat['secondLabel']) : null;
                                $secondValue = isset($stat['secondValue']) && $stat['secondValue'] !== '' ? $stat['secondValue'] : __('No value available');
                            @endphp
                            <div class="{{ VC::CL3 }} {{ VC::CS6 }}">
                                <div class="{{ VC::P4 }}">
                                    <p class="{{ VC::MB0 }}">{{ $label }}</p>
                                    <h6 class="report-text {{ VC::MB3 }}">{{ $value }}</h6>
                                    @if($secondLabel)
                                        <p class="{{ VC::MB0 }}">{{ $secondLabel }}</p>
                                        <h6 class="report-text {{ VC::MB0 }}">{{ $secondValue }}</h6>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        @if(empty($stats))
                            <div class="{{ VC::CL12 }}"><p class="{{ VC::TX_MUTED }}">{{ __('No company info available') }}</p></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <h5 class="{{ VC::MB4 }}">{{ __('Proposal') }}</h5>
                    <div class="table-responsive">
                        <table class="{{ VC::TB }}">
                            <thead>
                                <tr>
                                    <th>{{ __('Proposal') }}</th>
                                    <th>{{ __('Issue Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                        <th width="10%">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $hasDateFormat = method_exists($user,'dateFormat');
                                    $hasPriceFormat = method_exists($user,'priceFormat');
                                    $hasProposalNumber = method_exists($user,'proposalNumberFormat');
                                    $propRaw = (isset($customer) && method_exists($customer,'customerProposal')) ? $customer->customerProposal($customer->id) : [];
                                    $proposals = Utility::isFilled($propRaw ?? []) ? $propRaw : [];
                                    $statusBadgeClasses = [0=>'bg-primary',1=>'bg-warning',2=>'bg-danger',3=>'bg-info',4=>'bg-primary'];
                                @endphp
                                @forelse($proposals as $proposal)
                                    @php
                                        $pid = isset($proposal->id) ? $proposal->id : null;
                                        $showUrl = $pid ? route(ViewsConstants::PPS . '.show', Crypt::encrypt($pid)) : '#';
                                        $pnum = isset($proposal->proposal_id) ? $proposal->proposal_id : null;
                                        $issue = isset($proposal->issue_date) ? $proposal->issue_date : null;
                                        $amt = (isset($proposal) && method_exists($proposal,'getTotal')) ? $proposal->getTotal() : null;
                                        $pstatus = isset($proposal->status) ? $proposal->status : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ $showUrl }}" class="{{ VC::BT_OUTPM }}">{{ $pnum ? ($hasProposalNumber ? $user?->proposalNumberFormat($pnum) : __('Failed to format proposal number')) : __('No proposal number available') }}</a>
                                        </td>
                                        <td>{{ $issue ? ($hasDateFormat ? $user?->dateFormat($issue) : __('Failed to format date')) : __('No issue date available') }}</td>
                                        <td>{{ is_numeric($amt) ? ($hasPriceFormat ? $user?->priceFormat($amt) : __('Failed to format amount')) : __('No amount available') }}</td>
                                        <td>
                                            @if(isset($pstatus) && is_numeric($pstatus) && $pstatus >= 0 && $pstatus <= 4 && isset(Proposal::$statuses[$pstatus]))
                                                <span class="badge {{ $statusBadgeClasses[$pstatus] ?? 'bg-secondary' }} p-2 px-3 rounded">{{ __(Proposal::$statuses[$pstatus]) }}</span>
                                            @else
                                                <span class="badge bg-secondary p-2 px-3 rounded">{{ __('Unknown status') }}</span>
                                            @endif
                                        </td>
                                        @if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                            <td class="action"><span>@include('partials.proposal_actions', compact('proposal'))</span></td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><div class="{{ VC::TXCT }} {{ VC::TX_MUTED }}">{{ __('No proposals available') }}</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <h5 class="{{ VC::MB4 }}">{{ __('Invoice') }}</h5>
                    <div class="table-responsive">
                        <table class="{{ VC::TB }}">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Issue Date') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Due Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if(Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                        <th width="10%">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $hasInvoiceNumber = method_exists($user,'invoiceNumberFormat');
                                    $invRaw = (isset($customer) && method_exists($customer,'customerInvoice')) ? $customer->customerInvoice($customer->id) : [];
                                    $invoices = Utility::isFilled($invRaw ?? []) ? $invRaw : [];
                                    $statusBadgeClasses = [0=>'bg-primary',1=>'bg-warning',2=>'bg-danger',3=>'bg-info',4=>'bg-primary'];
                                @endphp
                                @forelse($invoices as $invoice)
                                    @php
                                        $iid = isset($invoice->id) ? $invoice->id : null;
                                        $showUrl = $iid ? route(ViewsConstants::INV . '.show', Crypt::encrypt($iid)) : '#';
                                        $guardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::INV, 'invoice_show_route_unavailable') ?? 'Invoice show route is unavailable. Please contact technical support or your domain administrator.';
                                        $inum = isset($invoice->invoice_id) ? $invoice->invoice_id : null;
                                        $issue = isset($invoice->issue_date) ? $invoice->issue_date : null;
                                        $due = isset($invoice->due_date) ? $invoice->due_date : null;
                                        $dueAmt = (isset($invoice) && method_exists($invoice,'getDue')) ? $invoice->getDue() : null;
                                        $istatus = isset($invoice->status) ? $invoice->status : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a id="invoice-show-btn-{{ $iid ?? 'x' }}" href="{{ $showUrl }}" data-url="{{ $showUrl }}" data-guard-msg="{{ $guardMsg }}" class="{{ VC::BT_OUTPM }}">{{ $inum ? ($hasInvoiceNumber ? $user?->invoiceNumberFormat($inum) : __('Failed to format invoice number')) : __('No invoice number available') }}</a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/customers/invoice.js') }}"></script>
                                            @endpush
                                        </td>
                                        <td>{{ $issue ? ($hasDateFormat ? $user?->dateFormat($issue) : __('Failed to format date')) : __('No issue date available') }}</td>
                                        <td>
                                            @if($due)
                                                @php $dueStr = $hasDateFormat ? $user?->dateFormat($due) : __('Failed to format date'); @endphp
                                                @if($due < date('Y-m-d')) <span class="text-danger">{{ $dueStr }}</span> @else {{ $dueStr }} @endif
                                            @else
                                                {{ __('No due date available') }}
                                            @endif
                                        </td>
                                        <td>{{ is_numeric($dueAmt) ? ($hasPriceFormat ? $user?->priceFormat($dueAmt) : __('Failed to format amount')) : __('No due amount available') }}</td>
                                        <td><span class="badge {{ (isset($istatus) && isset(Invoice::$statuses[$istatus])) ? ($statusBadgeClasses[$istatus] ?? 'bg-secondary') : 'bg-secondary' }} p-2 px-3 rounded">{{ (isset($istatus) && isset(Invoice::$statuses[$istatus])) ? __(Invoice::$statuses[$istatus]) : __('Unknown status') }}</span></td>
                                        @if(Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                            <td class="action"><span>@include('partials.invoice_actions', compact('invoice'))</span></td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="6"><div class="{{ VC::TXCT }} {{ VC::TX_MUTED }}">{{ __('No invoices available') }}</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
