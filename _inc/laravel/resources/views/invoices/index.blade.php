@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        LangsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Crypt, Gate, Route};
    use Illuminate\Support\{Collection, Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $invAv = !mepty($invoice) && isset($invoice->id);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Invoices')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Invoice')}}</li>
@endsection
@section(YieldingConstants::ADM_BDC)
    @php
        $dashUrl    = Route::has('dashboard') ? route('dashboard') : '#';
        $dashClass  = 'dashboard-link';
    @endphp
    <li class="breadcrumb-item">
        <a
            href="{{ $dashUrl }}"
            id="{{ $dashClass }}"
            class="{{ $dashClass }}"
            data-url="{{ $dashUrl }}"
            data-sv-localized="true"
            data-guard-msg="{{ __( Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') ?? '# ERROR' ) }}"
        >
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Invoice') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $exportUrl    = Route::has(ViewsConstants::INV.'.export')
                ? route(ViewsConstants::INV.'.export')
                : '#';
            $exportClass  = 'export-invoice-link';
        @endphp
        <a
            href="{{ $exportUrl }}"
            id="{{ $exportClass }}"
            class="{{ VC::BT_SM_PM }} {{ $exportClass }}"
            data-url="{{ $exportUrl }}"
            data-sv-localized="true"
            data-guard-msg="{{ __( Utility::fetchLinkMessage($lang, ViewsConstants::INV, 'invoice_export_route_unavailable') ?? '# ERROR' ) }}"
            data-bs-toggle="tooltip"
            title="{{ __('Export') }}"
        >
            <i class="{{ VC::TI_EXP }}"></i>
        </a>
        @can('create invoice')
            @php
                $createUrl   = Route::has(ViewsConstants::INV.'.create')
                    ? route(ViewsConstants::INV.'.create', 0)
                    : '#';
                $createClass = 'create-invoice-link';
            @endphp
            <a
                href="{{ $createUrl }}"
                id="{{ $createClass }}"
                class="{{ VC::BT_SM_PM }} {{ $createClass }}"
                data-url="{{ $createUrl }}"
                data-sv-localized="true"
                data-guard-msg="{{ __( Utility::fetchLinkMessage($lang, ViewsConstants::INV, 'invoice_create_route_unavailable') ?? '# ERROR' ) }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        @php
                            $submitRouteName            = ViewsConstants::INV . '.index';
                            $submitUrl                  = Route::has($submitRouteName)
                                ? route($submitRouteName)
                                : '#';
                            $formId                     = 'customer_submit';
                            $submitGuardKey             = 'invoice_index_route_unavailable';
                            $submitGuardMsg             = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::INV,
                                $submitGuardKey
                            ) ?? 'Invoice index route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        {{ Form::open([
                            'route'           => $submitUrl,
                            'method'        => 'GET',
                            'id'            => $formId,
                            'data-url'      => $submitUrl,
                            'data-guard-msg'=> $submitGuardMsg,
                        ]) }}
                            <div class="{{ VC::R_FLX_ALC_JCE }}">
                                <div class="{{ VC::CL_POS3 }}">
                                    <div class="btn-box">
                                        {{ Form::label('issue_date', __('Issue Date'),['class'=>'form-label'])}}
                                        {{ Form::date('issue_date', isset($_GET['issue_date'])?$_GET['issue_date']:'', array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1')) }}
                                    </div>
                                </div>
                                <div class="{{ VC::CL_POS3 }}">
                                        <div class="btn-box">
                                            {{ Form::label('customer', __('Customer'),['class'=>'form-label'])}}
                                            {{ Form::select('customer', Utility::isFilled($customer) ? $customer : [__('No customer available')], isset($_GET['customer']) ? $_GET['customer'] : '', ['class' => 'form-control select'] ?? []) }}
                                        </div>
                                    </div>
                                <div class="{{ VC::CL_XLG4 }}">
                                    <div class="btn-box">
                                        {{ Form::label('status', __('Status'),['class'=>'form-label'])}}
                                        {{ Form::select('status', [''=>'Select Status'] + (Utility::isFilled($customer) ? $status : [__('No status available')]),isset($_GET['status'])?$_GET['status']:'', array('class' => 'form-control select') ?? []) }}
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT_FEND }}">
                                    <a href="#" class="{{ VC::BT_SM_PM }}"
                                    onclick="document.getElementById('customer_submit').submit(); return false;"
                                    data-toggle="tooltip" data-original-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                    </a>
                                    @if($invAv)
                                        @php
                                            $resetLinkId = 'invoice-reset-link-'.$invoice->id;
                                        @endphp
                                        <a
                                            id="{{ $resetLinkId }}"
                                            href="{{ $submitUrl }}"
                                            class="{{ VC::BT_SM_DG }}"
                                            data-toggle="tooltip"
                                            title="{{ __('Reset') }}"
                                            data-url="{{ $submitUrl }}"
                                            data-guard-msg="{{ $submitGuardMsg }}"
                                            {{ $submitUrl === '#' ? 'aria-disabled="true"' : '' }}
                                        >
                                            <span class="btn-inner--icon">
                                                <i class="{{ VC::TI_TRS_OFF }}"></i>
                                            </span>
                                        </a>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const link = document.getElementById('{{ $resetLinkId }}');
                                                    if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                    link.setAttribute('data-listener-active', 'true');
                                        
                                                    link.addEventListener('click', event => {
                                                        try {
                                                            const url = link.getAttribute('data-url') ?? '#';
                                                            if (url !== '#') return;
                                                            event.preventDefault();
                                        
                                                            const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                        
                                                            link.setAttribute('data-failed-route', 'true');
                                                        } catch {}
                                                    });
                                                })();
                                            </script>
                                        @endpush
                                    @else
                                        <button class="{{ VC::BT_SM_PM }}" disabled>
                                            <span class="btn-inner--icon">
                                                <i class="{{ VC::TI_TRS_OFF }}"></i>
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        {{ Form::close() }}
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{asset('assets/js/routes/invoices/customers/submit.js')}}"></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th> {{ __('Invoice') }}</th>
                                    {{--                                @if (!\Auth::guard('customer')->check())--}}
                                    {{--                                    <th>{{ __('Customer') }}</th>--}}
                                    {{--                                @endif--}}
                                    <th>{{ __('Issue Date') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Due Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                    {{-- <th>
                                    <td class="barcode">
                                        {!! DNS1D::getBarcodeHTML($invoice->sku, "C128",1.4,22) !!}
                                        <p class="pid">{{$invoice->sku}}</p>
                                    </td>
                                </th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $canDateFormat = is_callable([$user, 'dateFormat']);
                                    $canPriceFormat = is_callable([$user, 'priceFormat']);
                                    $due = is_callable([$invoice, 'getDue']) ? $invoice->getDue() : null;
                                @endphp
                                @if(Utility::isFilled($invoices) ?? [])
                                    @foreach ($invoices as $invoice)
                                        <tr>
                                            <td class="Id">
                                                @php
                                                    $showRouteName              = ViewsConstants::INV . '.show';
                                                    $showInvoiceUrl             = Route::has($showRouteName)
                                                        ? route($showRouteName, Crypt::encrypt($invoice->id))
                                                        : '#';
                                                    $showInvoiceGuardMsg        = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::INV,
                                                        'invoice_show_route_unavailable'
                                                    ) ?? 'Invoice show route is unavailable. Please contact technical support or your domain administrator.';
                                                    $showInvoiceLinkId          = 'show-invoice-link-' . $invoice->id;
                                                @endphp
                                                <a
                                                    id="{{ $showInvoiceLinkId }}"
                                                    href="{{ $showInvoiceUrl }}"
                                                    class="{{ VC::BT_OUTPM }}"
                                                    data-url="{{ $showInvoiceUrl }}"
                                                    data-guard-msg="{{ $showInvoiceGuardMsg }}"
                                                    {{ $showInvoiceUrl === '#' ? 'aria-disabled="true"' : '' }}
                                                >
                                                    {{ is_callable([$user, 'invoiceNumberFormat']) ? $user->invoiceNumberFormat($invoice->invoice_id) : __('Failed to format invoice number') }}
                                                </a>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                        const linkEl = document.getElementById('{{ $showInvoiceLinkId }}');
                                                        if (!linkEl || linkEl.getAttribute('data-listener-active') === 'true') return;
                                                        linkEl.setAttribute('data-listener-active', 'true');
                                                        linkEl.addEventListener('click', event => {
                                                            try {
                                                            const url = linkEl.getAttribute('data-url') ?? '#';
                                                            if (url !== '#') return;
                                                    
                                                            event.preventDefault();
                                                            const msg           = linkEl.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                                    
                                                            linkEl.setAttribute('data-failed-route', 'true');
                                                            } catch (e) {}
                                                        });
                                                        })();
                                                    </script>
                                                @endpush
                                            </td>
                                            <td>{{ $canDateFormat ? (!empty($issue_date) ? $user->dateFormat($invoice->issue_date) : __('No issue date available')) : __('Failed to format issue date') }}</td>
                                            <td>
                                                @if (!empty($invoice->due_date) && $invoice->due_date < date('Y-m-d'))
                                                    <p class="text-danger mt-3">
                                                        {{ $canDateFormat ? $user->dateFormat($invoice->due_date) : __('Failed to format due date') }}</p>
                                                @else
                                                    {{ $canDateFormat ? (!empty($invoice->due_date) ? $user->dateFormat($invoice->due_date) : __('No due date available')) : __('Failed to format due date') }}
                                                @endif
                                            </td>
                                            <td>{{ $due && $canPriceFormat ? $user->priceFormat($invoice->getDue()) : __('Failed to get due value')  }}</td>
                                            <td>
                                                @php
                                                    $statusClasses = [
                                                        0 => 'bg-secondary',
                                                        1 => 'bg-warning', 
                                                        2 => 'bg-danger',
                                                        3 => 'bg-info',
                                                        4 => 'bg-primary'
                                                    ];
                                                @endphp
                                                <span class="status_badge badge {{ $statusClasses[$invoice->status] ?? 'bg-secondary' }} p-2 px-3 rounded">
                                                    {{ __(!empty(Invoice::$statuses) ? Invoice::$statuses[$invoice->status] : __('Failed to retrieve statuses')) }}
                                                </span>
                                            </td>
                                            @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                                <td class="Action">
                                                        <span>
                                                            @php $invoiceID= Crypt::encrypt($invoice->id); @endphp
                                                            @can('copy invoice')
                                                                @php
                                                                    $copyUrl     = Route::has(ViewsConstants::INV.'.link.copy')
                                                                        ? route(ViewsConstants::INV.'.link.copy', [$invoiceID])
                                                                        : '#';
                                                                    $copyClass   = 'copy-invoice-link';
                                                                    $copyId      = 'copy-invoice-link-'.$invoiceID;
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    <a
                                                                        href="{{ $copyUrl }}"
                                                                        id="{{ $copyId }}"
                                                                        class="{{ VC::BT_SM_CT }} {{ $copyClass }}"
                                                                        data-url="{{ $copyUrl }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ __( Utility::fetchLinkMessage($lang, ViewsConstants::INV, 'invoice_copy_route_unavailable') ?? '# ERROR' ) }}"
                                                                        onclick="copyToClipboard(this)"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Copy Invoice') }}"
                                                                        data-original-title="{{ __('Copy Invoice') }}"
                                                                    >
                                                                        <i class="ti ti-link text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('duplicate invoice')
                                                                @php
                                                                    $dupUrl       = Route::has(ViewsConstants::INV.'.duplicate')
                                                                        ? route(ViewsConstants::INV.'.duplicate', $invoice->id)
                                                                        : '#';
                                                                    $dupClass     = 'duplicate-invoice-link';
                                                                    $dupId        = 'duplicate-invoice-link-'.$invoice->id;
                                                                    $dupFormId    = 'duplicate-form-'.$invoice->id;
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    {!! Form::open([
                                                                        'method' => 'get',
                                                                        'url'    => $dupUrl,
                                                                        'id'     => $dupFormId
                                                                    ]) !!}
                                                                        <a
                                                                            href="{{ $dupUrl }}"
                                                                            id="{{ $dupId }}"
                                                                            class="{{ VC::BT_SM_CT_PR }} {{ $dupClass }}"
                                                                            data-url="{{ $dupUrl }}"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="{{ __( Utility::fetchLinkMessage($lang, ViewsConstants::INV, 'invoice_duplicate_route_unavailable') ?? '# ERROR' ) }}"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Duplicate') }}"
                                                                            data-original-title="{{ __('Duplicate') }}"
                                                                            data-confirm="{{ __( Utility::fetchLinkMessage($lang, 'generics', 'confirm_action_prompt' ) ) }}"
                                                                            data-confirm-yes="document.getElementById('{{ $dupFormId }}').submit();"
                                                                        >
                                                                            <i class="ti ti-copy text-white"></i>
                                                                        </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endcan
                                                            @can('show invoice')
                                                                    {{--                                                        @if (\Auth::guard('customer')->check())--}}
                                                                    {{--                                                            <div class="action-btn bg-info ms-2">--}}
                                                                    {{--                                                                    <a href="{{ route('customer.invoice.show', Crypt::encrypt($invoice->id)) }}"--}}
                                                                    {{--                                                                       class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Show "--}}
                                                                    {{--                                                                       data-original-title="{{ __('Detail') }}">--}}
                                                                    {{--                                                                        <i class="{{ VC::TI_EYE_WT }}"></i>--}}
                                                                    {{--                                                                    </a>--}}
                                                                    {{--                                                                </div>--}}
                                                                    {{--                                                        @else--}}
                                                                    @php
                                                                        $showUrl   = Route::has(ViewsConstants::INV.'.show')
                                                                            ? route(ViewsConstants::INV.'.show', Crypt::encrypt($invoice->id))
                                                                            : '#';
                                                                        $showClass = 'show-invoice-link';
                                                                        $showId    = 'show-invoice-link-'.$invoice->id;
                                                                    @endphp
                                                                    <div class="{{ VC::ACT_BTN_INF }}">
                                                                        <a
                                                                            href="{{ $showUrl }}"
                                                                            id="{{ $showId }}"
                                                                            class="{{ VC::BT_SM_CT }} {{ $showClass }}"
                                                                            data-url="{{ $showUrl }}"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="{{ __( Utility::fetchLinkMessage($lang, ViewsConstants::INV, 'invoice_show_route_unavailable') ?? '# ERROR' ) }}"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Detail') }}"
                                                                            data-original-title="{{ __('Detail') }}"
                                                                        >
                                                                            <i class="{{ VC::TI_EYE_WT }}"></i>
                                                                        </a>
                                                                    </div>
                                                                    {{-- @endif --}}
                                                            @endcan
                                                            @can('edit invoice')
                                                                @php
                                                                    $editUrl   = Route::has(ViewsConstants::INV.'.edit')
                                                                        ? route(ViewsConstants::INV.'.edit', Crypt::encrypt($invoice->id))
                                                                        : '#';
                                                                    $editClass = 'edit-invoice-link';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    <a
                                                                        href="{{ $editUrl }}"
                                                                        id="{{ $editClass }}-{{ $invoice->id }}"
                                                                        class="{{ VC::BT_SM_CT }} {{ $editClass }}"
                                                                        data-url="{{ $editUrl }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ __( Utility::fetchLinkMessage($lang, ViewsConstants::INV, 'invoice_edit_route_unavailable') ?? '# ERROR' ) }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        data-original-title="{{ __('Edit') }}"
                                                                    >
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('delete invoice')
                                                                @php
                                                                    $deleteUrl    = Route::has(ViewsConstants::INV.'.destroy')
                                                                        ? route(ViewsConstants::INV.'.destroy', $invoice->id)
                                                                        : '#';
                                                                    $deleteClass  = 'delete-invoice-link';
                                                                    $deleteFormId = 'delete-form-'.$invoice->id;
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'url'    => $deleteUrl,
                                                                        'id'     => $deleteFormId
                                                                    ]) !!}
                                                                        <a
                                                                            href="{{ $deleteUrl }}"
                                                                            id="{{ $deleteClass }}-{{ $invoice->id }}"
                                                                            class="{{ VC::BT_SM_CT_PR }} {{ $deleteClass }}"
                                                                            data-url="{{ $deleteUrl }}"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="{{ __( Utility::fetchLinkMessage($lang, ViewsConstants::INV, 'invoice_delete_route_unavailablee') ?? '# ERROR' ) }}"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                            data-original-title="{{ __('Delete') }}"
                                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                                        >
                                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                        </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endcan
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="{{ (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice')) ? 6 : 5 }}" class="text-center">
                                            {{ __('No records found') }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/invoices/clipboard.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/generics/dashboard.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/invoices/export.js') }}"></script>
    @can('create invoice')
        <script defer src="{{ asset('assets/js/routes/invoices/create.js') }}"></script>
    @endcan
    @can('copy invoice')
        <script defer src="{{ asset('assets/js/routes/invoices/copy.js') }}"></script>
    @endcan
    @can('duplicate invoice')
        <script defer src="{{ asset('assets/js/routes/invoices/duplicate.js') }}"></script>
    @endcan
    @can('show invoice')
        <script defer src="{{ asset('assets/js/routes/invoices/show.js') }}"></script>
    @endcan
    @can('edit invoice')
        <script defer src="{{ asset('assets/js/routes/invoices/edit.js') }}"></script>
    @endcan
    @can('delete invoice')
        <script defer src="{{ asset('assets/js/routes/invoices/delete.js') }}"></script>
    @endcan
@endpush
