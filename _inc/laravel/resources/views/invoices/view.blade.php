@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{ProductServiceUnit, Utility};
    use Illuminate\Support\Facades\{Auth, Crypt, Gate, Route, Storage, URL};
    use Illuminate\Support\{Collection, Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $invoiceIndexRouteName     = ViewsConstants::INV . '.index';
    $invoiceIndexUrl           = Route::has($invoiceIndexRouteName)
        ? route($invoiceIndexRouteName)
        : '#';
    $invoiceIndexGuardMsg      = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::INV,
        'invoice_index_route_unavailable'
    ) ?? 'Invoice index route is unavailable. Please contact technical support or your domain administrator.';
    $invAv = !empty($invoice) && isset($invoice->invoice_id);
    $invNumFmtAv = is_callable([$user, 'invoiceNumberFormat']);
    $valByNameAv = is_callable([Utility::class, 'getValByName']);
    $canFormatDate = is_callable([$user, 'dateFormat']);
    $canFormatPrice = is_callable([$user, 'priceFormat']);
    $settings = Utility::settings();
    $invHasStatus = $invAv && isset($invoice->status);
    $user_plan ??= $user?->{UsersConstants::COL_PL};
    $userPlanAv = !empty($user_plan) && isset($user_plan->id);
    $invoice_user ??= $invoice?->customer_id;
    $invoiceUserAv = !empty($invoice_user) && isset($invoice_user->id) ? $invoice_user : null;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Invoice Detail')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a
            id="breadcrumb-invoice-link"
            href="{{ $invoiceIndexUrl }}"
            {{ $invoiceIndexUrl === '#' ? 'aria-disabled="true"' : '' }}
            data-url="{{ $invoiceIndexUrl }}"
            data-guard-msg="{{ $invoiceIndexGuardMsg }}"
        >
            {{ __('Invoice') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ $invNumFmtAv ? $user->invoiceNumberFormat($invoice->invoice_id) : __('No invoice available') }}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('assets/css/routes/invoices/customer-invoice.css') }}" />
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script async src="https://js.stripe.com/v3/"></script>
    <script async src="https://js.paystack.co/v1/inline.js"></script>
    <script async src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
    <script async src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script async src="{{ asset('assets/js/routes/invoices/customers/lang/view.js') }}"></script>
    <script defer>
        (() => {
            const DATA_LISTENER_ADDED   = 'data-listener-added';
            const ERR_FB                = '# ERROR';
            const DATA_CLIENT_LOCALIZED = 'data-client-localized';
            const DATA_GUARD_MSG        = 'data-guard-msg';

            const getLocalizedMessage = (el, key) => {
                let msg = ERR_FB;
                if (el?.getAttribute('data-sv-localized') === 'true'
                    || el?.getAttribute(DATA_CLIENT_LOCALIZED) === 'true') {
                    msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
                } else {
                    let lang = (sessionStorage.getItem('erp-np-lang')
                                || document.documentElement.lang
                                || 'en')
                                .toLowerCase()
                                .replace(/_/g, '-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                    msg = window.translations?.[lang]?.[key]
                        || el.getAttribute(DATA_GUARD_MSG)
                        || window.translations?.['en']?.[key]
                        || ERR_FB;
                    if (msg !== ERR_FB) {
                        el.setAttribute(DATA_GUARD_MSG, msg);
                        el.setAttribute(DATA_CLIENT_LOCALIZED, 'true');
                    }
                }
                return msg;
            };

            const handleErrorDisplay = (el, key) => {
                const message = el
                    ? getLocalizedMessage(el, key)
                    : ERR_FB;
                const hasBootstrap = document.querySelector('link[href*="bootstrap"]')
                                    && window.bootstrap?.Toast;
                if (hasBootstrap) {
                    if (!document.querySelector('#error-toast')) {
                        const toast = document.createElement('div');
                        toast.id        = 'error-toast';
                        toast.className = 'toast align-items-center text-bg-danger border-0';
                        toast.setAttribute('role', 'alert');
                        toast.setAttribute('aria-live', 'assertive');
                        toast.setAttribute('aria-atomic', 'true');
                        toast.innerHTML = `
                            <div class="d-flex">
                                <div class="toast-body">${message}</div>
                                <button type="button"
                                        class="btn-close btn-close-white me-2 m-auto"
                                        data-bs-dismiss="toast"
                                        aria-label="Close"></button>
                            </div>`;
                        document.body.appendChild(toast);
                    }
                    new bootstrap.Toast(
                        document.querySelector('#error-toast')
                    ).show();
                } else {
                    alert(message);
                }
            };
            
            @if(!empty($company_payment_setting))
                try {
                    if (typeof $ === 'undefined') {
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) console.error("jQuery unavailable");
                        return;
                    }

                    @if(is_callable([$invoice, 'getDue']) && $invoice->getDue() > 0
                        && !empty($company_payment_setting)
                        && $company_payment_setting['is_stripe_enabled'] === 'on'
                        && $company_payment_setting['stripe_key']
                        && $company_payment_setting['stripe_secret']
                    )
                        try {
                            const stripeKey = '{{ $company_payment_setting["stripe_key"] }}';
                            if (!stripeKey) throw new Error();
                            const stripe = Stripe(stripeKey);
                            const elements = stripe.elements();
                            const style = { base: { fontSize: '14px', color: '#32325d' } };
                            const card = elements.create('card', { style });
                            card.mount('#card-element');
                            const form = document.getElementById('payment-form');
                            form.addEventListener('submit', async event => {
                                event.preventDefault();
                                const result = await stripe.createToken(card);
                                if (result.error) {
                                    $('#card-errors').html(result.error.message);
                                    show_toastr('error', result.error.message, 'error');
                                } else {
                                    const hidden = document.createElement('input');
                                    hidden.type = 'hidden';
                                    hidden.name = 'stripeToken';
                                    hidden.value = result.token.id;
                                    form.appendChild(hidden);
                                    form.submit();
                                }
                            });
                        } catch {
                            const el = document.getElementById('payment-form');
                            if (el && !el.hasAttribute(DATA_LISTENER_ADDED)) {
                                el.addEventListener('click',
                                    () => handleErrorDisplay(el, 'stripe_unavailable')
                                );
                                el.setAttribute(DATA_LISTENER_ADDED, 'true');
                                const obs = new MutationObserver((_, o) => {
                                    if (!document.body.contains(el)) {
                                        el.removeEventListener('click',
                                            () => handleErrorDisplay(el, 'stripe_unavailable')
                                        );
                                        o.disconnect();
                                    }
                                });
                                obs.observe(document.body, { childList: true, subtree: true });
                            }
                        }
                    @endif

                    @if(isset($company_payment_setting['paystack_public_key']))
                        $(document).on('click', '#pay_with_paystack', function() {
                            const el = this;
                            if (el.hasAttribute(DATA_LISTENER_ADDED)) return;
                            $('#paystack-payment-form').ajaxForm(res => {
                                try {
                                    if (res.flag === 1) {
                                        const handler = PaystackPop.setup({
                                            key: '{{ isset($company_payment_setting["paystack_public_key"]) ? $company_payment_setting["paystack_public_key"] : "" }}',
                                            email: res.email,
                                            amount: res.total_price * 100,
                                            currency: res.currency,
                                            ref: 'ps_ref_' + Math.floor(Math.random() * 1e9 + 1),
                                            metadata: { custom_fields: [{ display_name: 'Email', variable_name: 'email', value: res.email }] },
                                            callback: r => window.location.href =
                                                `{{ url('/invoices/paystack') }}/${r.reference}/{{ encrypt($invoice->id) }}?amount=${res.total_price}`,
                                            onClose: () => alert('window closed')
                                        });
                                        handler.openIframe();
                                    } else {
                                        toastrs('Error', res.msg || res.message, 'msg');
                                    }
                                } catch {
                                    handleErrorDisplay(el, 'paystack_unavailable');
                                }
                            }).submit();
                            el.setAttribute(DATA_LISTENER_ADDED, 'true');
                            const obs1 = new MutationObserver((_, o) => {
                                if (!document.body.contains(el)) {
                                    $(el).off('click');
                                    o.disconnect();
                                }
                            });
                            obs1.observe(document.body, { childList: true, subtree: true });
                        });
                    @endif

                    @if(isset($company_payment_setting['flutterwave_public_key']))
                        $(document).on('click', '#pay_with_flutterwave', function() {
                            const el = this;
                            if (el.hasAttribute(DATA_LISTENER_ADDED)) return;
                            $('#flutterwave-payment-form').ajaxForm(res => {
                                try {
                                    if (res.flag === 1) {
                                        const txref = Date.now() + '_' + Math.floor(Math.random() * 1e9);
                                        const x = getpaidSetup({
                                            PBFPubKey: '{{ isset($company_payment_setting["flutterwave_public_key"]) ? $company_payment_setting["flutterwave_public_key"] : '' }}',
                                            customer_email: '{{ $user?->email }}',
                                            amount: res.total_price,
                                            currency: '{{ $valByNameAv ? Utility::getValByName("site_currency") : '' }}',
                                            txref,
                                            meta: [{ metaname: 'payment_id', metavalue: 'id' }],
                                            callback: rsp => {
                                                if (['00','0'].includes(rsp.tx.chargeResponseCode)) {
                                                    window.location.href =
                                                        `{{ url('/invoices/flutterwave') }}/${rsp.tx.txRef}/{{ encrypt($invoice->id) }}`;
                                                }
                                                x.close();
                                            }
                                        });
                                    } else {
                                        toastrs('Error', res.msg || res.message, 'msg');
                                    }
                                } catch {
                                    handleErrorDisplay(el, 'flutterwave_unavailable');
                                }
                            }).submit();
                            el.setAttribute(DATA_LISTENER_ADDED, 'true');
                            const obs2 = new MutationObserver((_, o) => {
                                if (!document.body.contains(el)) {
                                    $(el).off('click');
                                    o.disconnect();
                                }
                            });
                            obs2.observe(document.body, { childList: true, subtree: true });
                        });
                    @endif

                    @if(isset($company_payment_setting['razorpay_public_key']))
                        $(document).on('click', '#pay_with_razorpay', function() {
                            const el = this;
                            if (el.hasAttribute(DATA_LISTENER_ADDED)) return;
                            $('#razorpay-payment-form').ajaxForm(res => {
                                try {
                                    if (res.flag === 1) {
                                        const options = {
                                            key: '{{ $company_payment_setting["razorpay_public_key"] }}',
                                            amount: res.total_price * 100,
                                            currency: '{{ Utility::getValByName("site_currency") }}',
                                            name: 'Plan',
                                            handler: r => window.location.href =
                                                `{{ url('/invoices/razorpay') }}/${r.razorpay_payment_id}/{{ encrypt($invoice->id) }}?amount=${res.total_price}`
                                        };
                                        new Razorpay(options).open();
                                    } else {
                                        toastrs('Error', res.msg || res.message, 'msg');
                                    }
                                } catch {
                                    handleErrorDisplay(el, 'razorpay_unavailable');
                                }
                            }).submit();
                            el.setAttribute(DATA_LISTENER_ADDED, 'true');
                            const obs3 = new MutationObserver((_, o) => {
                                if (!document.body.contains(el)) {
                                    $(el).off('click');
                                    o.disconnect();
                                }
                            });
                            obs3.observe(document.body, { childList: true, subtree: true });
                        });
                    @endif

                    $('.cp_link').on('click', function() {
                        const el = this;
                        try {
                            const value = $(el).attr('data-link') ?? '';
                            if (!navigator.clipboard) throw new Error();
                            navigator.clipboard.writeText(value);
                            show_toastr('success', '{{__("Link Copy on Clipboard")}}', 'success');
                        } catch {
                            handleErrorDisplay(el, 'link_copy_unavailable');
                        }
                    });

                    $(document).on('click', '#shipping', function() {
                        const el = this;
                        try {
                            const url = $(el).data('url') ?? '';
                            const isDisplay = $(el).is(':checked');
                            if (!url) return;
                            $.ajax({ url, type: 'get', data: { is_display: isDisplay } });
                        } catch {
                            handleErrorDisplay(el, 'shipping_toggle_unavailable');
                        }
                    });

                } catch (e) {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error('Initialization failed', e);
                }
            @endif
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_CTT)
    @if($invAv)
        @php
            $issueDate = $canFormatDate ? (!empty($invoice->issue_date) ? $user?->dateFormat($invoice->issue_date) : __('No issue date available')) : __('Failed to format date');
            $dueDate  = $canFormatDate ? (!empty($invoice->due_date) ? $user?->dateFormat($invoice->due_date) : __('No due date available')) : __('Failed to format date');
        @endphp
        @can('send invoice')
            @if($invHasStatus && $invoice->status!=4)
                <div class="{{ VC::RW }}">
                    <div class="{{ VC::C12 }}">
                        <div class="{{ VC::CD }}">
                            <div class="card-body">
                                <div class="{{ VC::RW }} timeline-wrapper">
                                    <div class="col-md-6 col-lg-4 col-xl-4">
                                        <div class="timeline-icons">
                                            <span class="timeline-dots"></span>
                                            <i class="ti ti-plus text-primary"></i>
                                        </div>
                                        <h6 class="text-primary my-3">{{ __('Create Invoice') }}</h6>
                                        <p class="text-muted text-sm mb-3">
                                            <i class="ti ti-clock me-2"></i>
                                            {{ __('Created on ') }}{{ $issueDate }}
                                        </p>
                                        @can('edit invoice')
                                            @php
                                                $editRouteName        = ViewsConstants::INV . '.edit';
                                                $editUrl              = Route::has($editRouteName)
                                                    ? route($editRouteName, Crypt::encrypt($invoice->id))
                                                    : '#';
                                                $linkId               = 'invoice-edit-link-' . $invoice->id;
                                                $editGuardMsg         = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::INV,
                                                    'invoice_edit_route_unavailable'
                                                ) ?? 'Invoice edit route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <a
                                                id="{{ $linkId }}"
                                                href="{{ $editUrl }}"
                                                class="{{ VC::BT_SM_PM }}"
                                                data-url="{{ $editUrl }}"
                                                data-guard-msg="{{ $editGuardMsg }}"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Edit') }}"
                                                {{ $editUrl === '#' ? 'aria-disabled="true"' : '' }}
                                            >
                                                <i class="{{ ViewClassNamesConstants::TI_PC }} me-2"></i>{{ __('Edit') }}
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const link = document.getElementById('{{ $linkId }}');
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
                                                            } catch (e) {}
                                                        });
                                                    })();
                                                </script>
                                            @endpush
                                        @endcan
                                    </div>
                
                                    <div class="col-md-6 col-lg-4 col-xl-4">
                                        <div class="timeline-icons">
                                            <span class="timeline-dots"></span>
                                            <i class="ti ti-mail text-warning"></i>
                                        </div>
                                        <h6 class="text-warning my-3">{{ __('Send Invoice') }}</h6>
                                        <p class="text-muted text-sm mb-3">
                                            @if($invoice->status != 0)
                                                <i class="ti ti-clock me-2"></i>
                                                {{ __('Sent on') }} {{ $canFormatAv ? ($invoice->send_date ? $user?->dateFormat($invoice->send_date) : __('No send date available')) : __('Failed to format send date') }}
                                            @else
                                                @can('send invoice')
                                                    <small>{{ __('Status') }} : {{ __('Not Sent') }}</small>
                                                @endcan
                                            @endif
                                        </p>

                                        @if($invoice->status == 0)
                                            @can('send bill')
                                                @php
                                                    $markSentRouteName       = ViewsConstants::INV . '.sent';
                                                    $markSentUrl             = Route::has($markSentRouteName)
                                                        ? route($markSentRouteName, $invoice->id)
                                                        : '#';
                                                    $markSentLinkId          = 'invoice-mark-sent-' . $invoice->id;
                                                    $markSentGuardMsg        = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::INV,
                                                        'invoice_mark_sent_route_unavailable'
                                                    ) ?? 'Send invoice route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <a
                                                    id="{{ $markSentLinkId }}"
                                                    href="{{ $markSentUrl }}"
                                                    class="btn btn-sm btn-warning"
                                                    data-url="{{ $markSentUrl }}"
                                                    data-guard-msg="{{ $markSentGuardMsg }}"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Mark Sent') }}"
                                                    {{ $markSentUrl === '#' ? 'aria-disabled="true"' : '' }}
                                                >
                                                    <i class="ti ti-send me-2"></i>{{ __('Send') }}
                                                </a>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            const link = document.getElementById('{{ $markSentLinkId }}');
                                                            if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                            link.setAttribute('data-listener-active', 'true');
                                                
                                                            link.addEventListener('click', event => {
                                                                try {
                                                                    const url = link.getAttribute('data-url') ?? '#';
                                                                    if (url !== '#') return;
                                                                    event.preventDefault();
                                                
                                                                    const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                    let container       = document.getElementById('toast-container');
                                                                    if (!container) {
                                                                        container       = document.createElement('div');
                                                                        container.id    = 'toast-container';
                                                                        document.body.appendChild(container);
                                                                    }
                                                
                                                                    if (bsLink && window.bootstrap) {
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
                                                                } catch (e) {}
                                                            });
                                                        })();
                                                    </script>
                                                @endpush
                                            @endcan
                                        @endif
                                    </div>
                                    <div class="col-md-6 col-lg-4 col-xl-4">
                                        <div class="timeline-icons">
                                            <span class="timeline-dots"></span>
                                            <i class="ti ti-report-money text-info"></i>
                                        </div>
                                        <h6 class="text-info my-3">{{ __('Get Paid') }}</h6>
                                        <p class="text-muted text-sm mb-3">
                                            {{ __('Status') }} : {{ __('Awaiting payment') }}
                                        </p>
                                        @if($invoice->status != 0)
                                            @can('create payment invoice')
                                                @php
                                                    $addPaymentRouteName      = ViewsConstants::INV . '.payment';
                                                    $addPaymentUrl            = Route::has($addPaymentRouteName)
                                                        ? route($addPaymentRouteName, $invoice->id)
                                                        : '#';
                                                    $addPaymentGuardMsg       = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::INV,
                                                        'invoice_payment_route_unavailable'
                                                    ) ?? 'Add payment route is unavailable. Please contact technical support or your domain administrator.';
                                                    $addPaymentLinkId         = 'add-payment-link-' . $invoice->id;
                                                @endphp
                                                <a
                                                    id="{{ $addPaymentLinkId }}"
                                                    href="#"
                                                    class="{{ VC::BT_SM_PM }}"
                                                    data-url="{{ $addPaymentUrl }}"
                                                    data-ajax-popup="true"
                                                    data-title="{{ __('Add Payment') }}"
                                                    data-guard-msg="{{ $addPaymentGuardMsg }}"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Receive Payment') }}"
                                                >
                                                    <i class="ti ti-report-money me-2"></i>{{ __('Receive Payment') }}
                                                </a>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            const link = document.getElementById('{{ $addPaymentLinkId }}');
                                                            if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                            link.setAttribute('data-listener-active', 'true');
                                                
                                                            link.addEventListener('click', event => {
                                                                try {
                                                                    const url = link.getAttribute('data-url') ?? '#';
                                                                    if (url !== '#') return; // valid route, let AJAX popup proceed
                                                
                                                                    event.preventDefault();
                                                                    const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                    let container       = document.getElementById('toast-container');
                                                                    if (!container) {
                                                                        container       = document.createElement('div');
                                                                        container.id    = 'toast-container';
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bsLink && window.bootstrap) {
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
                                                                } catch (e) {}
                                                            });
                                                        })();
                                                    </script>
                                                @endpush
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endcan
        @if (Gate::check('show invoice'))
            @if($invHasStatus && $invoice->status != 0)
                <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }} {{ VC::MB3 }}">
                    <div class="col-md-12 {{ VC::DFL }} {{ VC::ALC }} {{ VC::JCB }} justify-content-md-end">
                        @if(!empty($invoicePayment))
                            <div class="all-button-box mx-2 {{ VC::MR2 }}">
                                @php
                                    $addCreditNoteRouteName        = ViewsConstants::INV . '.credit.note';
                                    $addCreditNoteUrl              = Route::has($addCreditNoteRouteName)
                                        ? route($addCreditNoteRouteName, $invoice->id)
                                        : '#';
                                    $addCreditNoteLinkId           = 'add-credit-note-link-' . $invoice->id;
                                    $addCreditNoteGuardMsg         = Utility::fetchLinkMessage(
                                        $lang,
                                        ViewsConstants::INV,
                                        'credit_note_route_unavailable'
                                    ) ?? 'Add Credit Note route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                <a
                                    id="{{ $addCreditNoteLinkId }}"
                                    href="#"
                                    class="{{ VC::BT_SM_PM }}"
                                    data-url="{{ $addCreditNoteUrl }}"
                                    data-ajax-popup="true"
                                    data-title="{{ __('Add Credit Note') }}"
                                    data-guard-msg="{{ $addCreditNoteGuardMsg }}"
                                    {{ $addCreditNoteUrl === '#' ? 'aria-disabled="true"' : '' }}
                                >
                                    {{ __('Add Credit Note') }}
                                </a>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer>
                                        (() => {
                                            const link = document.getElementById('{{ $addCreditNoteLinkId }}');
                                            if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                            link.setAttribute('data-listener-active', 'true');
                                
                                            link.addEventListener('click', event => {
                                                try {
                                                    const url = link.getAttribute('data-url') ?? '#';
                                                    if (url !== '#') return; // valid route, proceed with AJAX popup
                                
                                                    event.preventDefault();
                                                    const msg           = link.getAttribute('data-guard-msg') || '# ERROR';
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
                                
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                
                                                    link.setAttribute('data-failed-route', 'true');
                                                } catch (e) {}
                                            });
                                        })();
                                    </script>
                                @endpush
                            </div>
                        @endif
                        @if($invoice->status!= 4)
                            @php
                                $routeName         = ViewsConstants::INV . '.payment.reminder';
                                $reminderUrl       = Route::has($routeName)
                                    ? route($routeName, $invoice->id)
                                    : (Route::has(Str::kebab($routeName))
                                        ? route(Str::kebab($routeName), $invoice->id)
                                        : '#');
                                $reminderLinkId    = 'receipt-reminder-link-' . $invoice->id;
                                $guardMsg          = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::INV,
                                    'payment_reminder_route_unavailable'
                                ) ?? 'Receipt reminder route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <div class="all-button-box {{ VC::MR2 }}">
                                <a
                                    id="{{ $reminderLinkId }}"
                                    href="{{ $reminderUrl }}"
                                    class="{{ VC::BT_SM_PM }} me-2"
                                    data-url="{{ $reminderUrl }}"
                                    data-guard-msg="{{ $guardMsg }}"
                                >
                                    {{ __('Receipt Reminder') }}
                                </a>
                            </div>
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const link = document.getElementById('{{ $reminderLinkId }}');
                                        if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                        link.setAttribute('data-listener-active', 'true');
                            
                                        link.addEventListener('click', event => {
                                            try {
                                                const url = link.getAttribute('data-url') || '#';
                                                if (url !== '#') return;
                            
                                                event.preventDefault();
                                                const msg = link.getAttribute('data-guard-msg') || '# ERROR';
                                                const bsLink = document.querySelector('link[href*="bootstrap"]');
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                            
                                                if (bsLink && window.bootstrap) {
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
                            
                                                link.setAttribute('data-failed-route', 'true');
                                            } catch (e) {}
                                        });
                                    })();
                                </script>
                            @endpush
                        @endif
                        <div class="all-button-box {{ VC::MR2 }}">
                            @php
                                $resendRouteName        = ViewsConstants::INV . '.resent';
                                $resendUrl              = Route::has($resendRouteName)
                                    ? route($resendRouteName, $invoice->id)
                                    : '#';
                                $resendLinkId           = 'invoice-resend-link-' . $invoice->id;
                                $resendGuardMsg         = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::INV,
                                    'invoice_resend_route_unavailable'
                                ) ?? 'Resend invoice route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <a
                                id="{{ $resendLinkId }}"
                                href="{{ $resendUrl }}"
                                class="{{ VC::BT_SM_PM }} me-2"
                                data-url="{{ $resendUrl }}"
                                data-guard-msg="{{ $resendGuardMsg }}"
                                {{ $resendUrl === '#' ? 'aria-disabled="true"' : '' }}
                            >
                                {{ __('Resend Invoice') }}
                            </a>
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const link = document.getElementById('{{ $resendLinkId }}');
                                        if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                        link.setAttribute('data-listener-active', 'true');
                            
                                        link.addEventListener('click', event => {
                                            try {
                                                const url = link.getAttribute('data-url') ?? '#';
                                                if (url !== '#') return;
                                                event.preventDefault();
                            
                                                const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                                                const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                let container       = document.getElementById('toast-container');
                                                if (!container) {
                                                    container       = document.createElement('div');
                                                    container.id    = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                            
                                                if (bsLink && window.bootstrap) {
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
                                            } catch (e) {}
                                        });
                                    })();
                                </script>
                            @endpush
                        </div>
                        <div class="all-button-box">
                            @php
                                $pdfRouteName           = ViewsConstants::INV . '.pdf';
                                $pdfUrl                 = Route::has($pdfRouteName)
                                    ? route($pdfRouteName, Crypt::encrypt($invoice->id))
                                    : '#';
                                $pdfLinkId              = 'invoice-download-link-' . $invoice->id;
                                $pdfGuardMsg            = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::INV,
                                    'invoice_pdf_route_unavailable'
                                ) ?? 'Download invoice PDF route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <a
                                id="{{ $pdfLinkId }}"
                                href="{{ $pdfUrl }}"
                                target="_blank"
                                class="{{ VC::BT_SM_PM }}"
                                data-url="{{ $pdfUrl }}"
                                data-guard-msg="{{ $pdfGuardMsg }}"
                                {{ $pdfUrl === '#' ? 'aria-disabled="true"' : '' }}
                            >
                                {{ __('Download') }}
                            </a>
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const link = document.getElementById('{{ $pdfLinkId }}');
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
                                            } catch (e) {}
                                        });
                                    })();
                                </script>
                            @endpush
                        </div>
                    </div>
                </div>
            @endif
        @endif
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD }} card-body">
                        <div class="invoice">
                            <div class="invoice-print">
                                <div class="{{ VC::RW }} invoice-title {{ VC::MT4 }}">
                                    <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                        <h4>{{__('Invoice')}}</h4>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                        <h4 class="invoice-number">{{ $invNumFmtAv ? $user?->invoiceNumberFormat($invoice->invoice_id) : __('Failed to format invoice number') }}</h4>
                                    </div>
                                    <div class="{{ VC::C12 }}">
                                        <hr>
                                    </div>
                                </div>
                                <div class="{{ VC::RW }}">
                                    <div class="col text-end">
                                        <div class="{{ VC::DFL }} {{ VC::ALC }} {{ VC::JCE }}">
                                            <div class="{{ VC::ME3 }}">
                                                <small>
                                                    <strong>{{__('Issue Date')}} :</strong><br>
                                                    {{ $issueDate }}<br><br>
                                                </small>
                                            </div>
                                            <div>
                                                <small>
                                                    <strong>{{__('Due Date')}} :</strong><br>
                                                    {{ $dueDate }}<br><br>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::RW }}">
                                    @if(!empty($customer))
                                        <div class="col">
                                            <small class="font-style">
                                                <strong>{{__('Billed To')}} :</strong><br>
                                                @if(!empty($customer->billing_name))
                                                    {{!empty($customer->billing_name)?$customer->billing_name: __('No name for billing available')}}<br>
                                                    {{!empty($customer->billing_address)?$customer->billing_address:__('No address for billing available')}}<br>
                                                    {{!empty($customer->billing_city)?$customer->billing_city:__('No city for billing available') .', '}}<br>
                                                    {{!empty($customer->billing_state)?$customer->billing_state:__('No state for billing available') .', '}},
                                                    {{!empty($customer->billing_zip)?$customer->billing_zip:__('No zip for billing available')}}<br>
                                                    {{!empty($customer->billing_country)?$customer->billing_country:__('No country for billing available')}}<br>
                                                    {{!empty($customer->billing_phone)?$customer->billing_phone:__('No phone for billing available')}}<br>
                                                    @if(!empty($settings['vat_gst_number_switch']) && $settings['vat_gst_number_switch'] === 'on')
                                                        <strong>{{__('Tax Number ')}} : </strong>{{!empty($customer->tax_number)?$customer->tax_number:__('No tax number for billing available')}}
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </small>
                                        </div>
                                    @if(Utility::getValByName('shipping_display')=='on')
                                        <div class="col ">
                                            <small>
                                                <strong>{{__('Shipped To')}} :</strong><br>
                                                @if(!empty($customer->shipping_name))
                                                {{!empty($customer->shipping_name)?$customer->shipping_name:__('No name for shipping available')}}<br>
                                                {{!empty($customer->shipping_address)?$customer->shipping_address:__('No address for shipping available')}}<br>
                                                {{!empty($customer->shipping_city)?$customer->shipping_city:__('No city for shipping available') .', '}}<br>
                                                {{!empty($customer->shipping_state)?$customer->shipping_state:__('No state for shipping available') .', '}},
                                                {{!empty($customer->shipping_zip)?$customer->shipping_zip:__('No zip for shipping available')}}<br>
                                                {{!empty($customer->shipping_country)?$customer->shipping_country:__('No country for shipping available')}}<br>
                                                {{!empty($customer->shipping_phone)?$customer->shipping_phone:''}}<br>
                                                @else
                                                    -
                                                @endif
                                            </small>
                                        </div>
                                    @endif
                                    @else
                                        <div class="col"><small>{{ __('No customer data available') }}</small></div>
                                    @endif
                                    <div class="col">
                                        @php
                                            $copyRouteName          = ViewsConstants::INV . '.link.copy';
                                            $encryptedInvoiceId     = Crypt::encrypt($invoice->id);
                                            $copyUrl                = Route::has($copyRouteName)
                                                ? route($copyRouteName, $encryptedInvoiceId)
                                                : '#';
                                            $copyGuardMsg           = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::INV,
                                                'invoice_copy_route_unavailable'
                                            ) ?? 'Invoice link copy route is unavailable. Please contact technical support or your domain administrator.';
                                            $copyLinkId             = 'invoice-copy-link-' . $invoice->id;
                                        @endphp
                                        <div class="{{ VC::FEND }} {{ VC::MT3 }}">
                                            <a
                                                id="{{ $copyLinkId }}"
                                                href="{{ $copyUrl }}"
                                                data-url="{{ $copyUrl }}"
                                                data-guard-msg="{{ $copyGuardMsg }}"
                                                {{ $copyUrl === '#' ? 'aria-disabled="true"' : '' }}
                                            >
                                                {!! class_exists(\Milon\Barcode\DNS2D::class) && is_callable([\Milon\Barcode\DNS2D, 'getBarcodeHTML']) ? DNS2D::getBarcodeHTML($copyUrl, "QRCODE", 2, 2) : __('Failed to generate QR code') !!}
                                            </a>
                                        </div>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const link = document.getElementById('{{ $copyLinkId }}');
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
                                                        } catch (e) {}
                                                    });
                                                })();
                                            </script>
                                        @endpush
                                    </div>
                                </div>
                                <div class="{{ VC::RW }} {{ VC::MT3 }}">
                                    <div class="col">
                                        <small>
                                            <strong>{{__('Status')}} :</strong><br>
                                            @if(!empty($invoice->status) && !empty(Invoice::$statuses))
                                                @if($invoice->status == 0)
                                                    <span class="{{ VC::BDG }} {{ VC::BG_P }}">{{ __(Invoice::$statuses[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 1)
                                                    <span class="{{ VC::BDG }} bg-warning">{{ __(Invoice::$statuses[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 2)
                                                    <span class="{{ VC::BDG }} bg-danger">{{ __(Invoice::$statuses[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 3)
                                                    <span class="{{ VC::BDG }} bg-info">{{ __(Invoice::$statuses[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 4)
                                                    <span class="{{ VC::BDG }} {{ VC::BG_P }}">{{ __(Invoice::$statuses[$invoice->status]) }}</span>
                                                @else
                                                    <span class="{{ VC::BDG }} {{ VC::BG_S }}">{{ __('Unknown status') }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">{{ __('Failed to get status') }}</span>
                                        </small>
                                    </div>
                                    @if(!empty($customFields) && ((is_array($invoice->customField) && count($invoice->customField)) || ($invoice->customField instanceof Collection && $invoice->customField->isNotEmpty())))
                                        @foreach($customFields as $field)
                                            <div class="col text-md-right">
                                                <small>
                                                    <strong>{{!empty($field->name) ? $field->name : __('No name available for field')}} :</strong><br>
                                                    {{!empty($invoice->customField)?$invoice->customField[$field->id]:__('No value available for field')}}
                                                    <br><br>
                                                </small>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-muted">{{ __('No custom fields available') }}</div>
                                    @endif
                                </div>
                                <div class="{{ VC::RW }} {{ VC::MT4 }}">
                                    <div class="{{ VC::CM12 }}">
                                        <div class="font-weight-bold">{{__('Product Summary')}}</div>
                                        <small>{{__('No items in the table below can be deleted.')}}</small>
                                        @php
                                            $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);
                                        @endphp
                                        <div class="table-responsive {{ VC::MT2 }}">
                                            <table class="{{ VC::TB }} {{ VC::MB0 }} table-striped">
                                                <tr>
                                                    <th data-width="40" class="text-dark">#</th>
                                                    <th class="text-dark">{{ __('Product') }}</th>
                                                    <th class="text-dark">{{ __('Quantity') }}</th>
                                                    <th class="text-dark">{{ __('Rate') }}</th>
                                                    <th class="text-dark">{{ __('Discount') }}</th>
                                                    <th class="text-dark">{{ __('Tax') }}</th>
                                                    <th class="text-dark">{{ __('Description') }}</th>
                                                    <th class="{{ VC::TXT_END }} text-dark" width="12%">{{ __('Price') }}<br><small class="text-danger font-weight-bold">{{ __('after tax & discount') }}</small></th>
                                                </tr>
                                                @php
                                                    $itemsSafe = (is_array($items ?? null) && count($items ?? [])) ? $items : (($items ?? null) instanceof Collection && $items->isNotEmpty() ? $items : []);
                                                    $totalQuantity=0; $totalRate=0; $totalTaxPrice=0; $totalDiscount=0; $taxesData=[];
                                                @endphp

                                                @if(!empty($itemsSafe))
                                                    @foreach($itemsSafe as $idx => $item)
                                                        @php
                                                            $product      = data_get($item,'product');
                                                            $productName  = data_get($product,'name', __('No product name available'));
                                                            $unitId       = data_get($product,'unit_id');
                                                            $unitName     = optional(ProductServiceUnit::find($unitId))->name ?? __('No unit');
                                                            $qty          = (float) ($item->quantity ?? 0);
                                                            $rate         = (float) ($item->price ?? 999999999999.99);
                                                            $disc         = (float) ($item->discount ?? 0);
                                                            $totalQuantity += $qty; $totalRate += $rate; $totalDiscount += $disc;
                                                            $taxRows = []; $itemTaxTotal = 0;
                                                            $itemTaxes = !empty($item->tax) ? (Utility::tax($item->tax) ?? []) : [];
                                                            foreach($itemTaxes as $tx){
                                                                $txPrice = (float) Utility::taxRate($tx->rate, $rate, $qty, $disc);
                                                                $itemTaxTotal += $txPrice; $totalTaxPrice += $txPrice;
                                                                $taxesData[$tx->name] = ($taxesData[$tx->name] ?? 0) + $txPrice;
                                                                $taxRows[] = [$tx->name, $tx->rate, $txPrice];
                                                            }
                                                            $rowTotal = ($rate * $qty - $disc) + $itemTaxTotal;
                                                            $descOut  = trim((string)($item->description ?? '')) !== '' ? $item->description : '-';
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $idx + 1 }}</td>
                                                            <td>{{ $productName }}</td>
                                                            <td>{{ $qty }} ({{ $unitName }})</td>
                                                            <td>{{ $canFormatPrice ? $user?->priceFormat($rate) : __('Failed to format tax rate') }}</td>
                                                            <td>{{ $canFormatPrice ? $user?->priceFormat($disc) : __('Failed to format tax discount') }}</td>
                                                            <td>
                                                                @if(!empty($taxRows))
                                                                    <table>
                                                                        @foreach($taxRows as [$tName,$tRate,$tPrice])
                                                                            <tr>
                                                                                <td>{{ $tName.' ('.$tRate.'%)' }}</td>
                                                                                <td>{{ $canFormatPrice ? $user?->priceFormat($tPrice) : __('Failed to format tax amount') }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </table>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>{{ $descOut }}</td>
                                                            <td class="{{ VC::TXT_END }}">{{ $canFormatPrice ? $user?->priceFormat($rowTotal) : __('Failed to format tax total') }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="8" class="text-center">{{ __('No invoice items found.') }}</td>
                                                    </tr>
                                                @endif

                                                @php
                                                    $subTotal      = is_callable([$invoice,'getSubTotal'])        ? (float) $invoice->getSubTotal()        : 0.0;
                                                    $totalDiscountI= is_callable([$invoice,'getTotalDiscount'])   ? (float) $invoice->getTotalDiscount()   : 0.0;
                                                    $grandTotal    = is_callable([$invoice,'getTotal'])           ? (float) $invoice->getTotal()           : 0.0;
                                                    $creditNote    = is_callable([$invoice,'invoiceTotalCreditNote']) ? (float) $invoice->invoiceTotalCreditNote() : 0.0;
                                                    $dueAmt        = is_callable([$invoice,'getDue'])             ? (float) $invoice->getDue()             : max(0, $grandTotal - $creditNote);
                                                    $paidAmt       = max(0, $grandTotal - $dueAmt - $creditNote);
                                                @endphp

                                                <tfoot>
                                                    <tr>
                                                        <td></td>
                                                        <td><b>{{ __('Total') }}</b></td>
                                                        <td><b>{{ $totalQuantity }}</b></td>
                                                        <td><b>{{ $canFormatPrice ? $user?->priceFormat($totalRate) : __('Failed to format total rate') }}</b></td>
                                                        <td><b>{{ $canFormatPrice ? $user?->priceFormat($totalDiscount) : __('Failed to format total discount') }}</b></td>
                                                        <td><b>{{ $canFormatPrice ? $user?->priceFormat($totalTaxPrice) : __('Failed to format total tax') }}</b></td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="{{ VC::TXT_END }}"><b>{{ __('Sub Total') }}</b></td>
                                                        <td class="{{ VC::TXT_END }}">{{ $canFormatPrice ? $user?->priceFormat($subTotal) : __('Failed to format sub total') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="{{ VC::TXT_END }}"><b>{{ __('Discount') }}</b></td>
                                                        <td class="{{ VC::TXT_END }}">{{ $canFormatPrice ? $user?->priceFormat($totalDiscountI) : __('Failed to format total discount') }}</td>
                                                    </tr>
                                                    @if(!empty($taxesData))
                                                        @foreach($taxesData as $taxName => $taxPrice)
                                                            <tr>
                                                                <td colspan="6"></td>
                                                                <td class="{{ VC::TXT_END }}"><b>{{ $taxName }}</b></td>
                                                                <td class="{{ VC::TXT_END }}">{{ $canFormatPrice ? $user?->priceFormat($taxPrice) : __('Failed to format tax amount') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="blue-text {{ VC::TXT_END }}"><b>{{ __('Total') }}</b></td>
                                                        <td class="blue-text {{ VC::TXT_END }}">{{ $canFormatPrice ? $user?->priceFormat($grandTotal) : __('Failed to format grand total') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="{{ VC::TXT_END }}"><b>{{ __('Paid') }}</b></td>
                                                        <td class="{{ VC::TXT_END }}">{{ $canFormatPrice ? $user?->priceFormat($paidAmt) : __('Failed to format paid amount') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="{{ VC::TXT_END }}"><b>{{ __('Credit Note') }}</b></td>
                                                        <td class="{{ VC::TXT_END }}">{{ $canFormatPrice ? $user?->priceFormat($creditNote) : __('Failed to format credit note') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="{{ VC::TXT_END }}"><b>{{ __('Due') }}</b></td>
                                                        <td class="{{ VC::TXT_END }}">{{ $canFormatPrice ? $user?->priceFormat($dueAmt) : __('Failed to format due amount') }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD }} card-body table-border-style">
                        <h5 class="d-inline-block">{{__('Receipt Summary')}}</h5><br>
                        @if(!$invoiceUserAv || !$userPlanAv)
                            <small>{{ __('Failed to access plans data') }}</small><br />
                        @else
                            @if($user_plan->storage_limit <= $invoice_user->storage_limit)
                                <small class="text-danger font-bold">{{__('Your plan storage limit is over , so you can not see customer uploaded payment receipt')}}</small><br>
                            @else
                                <small>{{ __('You can see customer uploaded payment receipt until your plan storage limit is over') }}</small><br />
                            @endif
                        @endif
                        <div class="table-responsive {{ VC::MT3 }}">
                            <table class="{{ VC::TB }}">
                                <thead>
                                    <tr>
                                        <th class="text-dark">{{__('Payment Receipt')}}</th>
                                        <th class="text-dark">{{__('Date')}}</th>
                                        <th class="text-dark">{{__('Amount')}}</th>
                                        <th class="text-dark">{{__('Payment Type')}}</th>
                                        <th class="text-dark">{{__('Account')}}</th>
                                        <th class="text-dark">{{__('Reference')}}</th>
                                        <th class="text-dark">{{__('Description')}}</th>
                                        <th class="text-dark">{{__('Receipt')}}</th>
                                        <th class="text-dark">{{__('OrderId')}}</th>
                                        @can('delete payment invoice')
                                            <th class="text-dark">{{__('Action')}}</th>
                                        @endcan
                                    </tr>
                                </thead>
                                @php
                                    $path = Utility::getFile('uploads/order');
                                @endphp
                                @if(Utility::isFilled($invoice->payments) ?? [])
                                    @foreach($invoice->payments as $key =>$payment)
                                        <tr>
                                            <td>
                                                @if(!empty($payment->add_receipt))
                                                    @php
                                                        $paymentAddReceipt = $payment->add_receipt;
                                                        $receiptAssetUrl   = $paymentAddReceipt
                                                            ? asset(Storage::url("uploads/payment/{$paymentAddReceipt}"))
                                                            : '#';
                                                        $linkId            = 'payment-add-receipt-' . $payment->id;
                                                        $guardMsg          = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::INV,
                                                            'payment_add_receipt_download_route_unavailable'
                                                        ) ?? 'Payment receipt download route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <td>
                                                        @if($receiptAssetUrl !== '#')
                                                            <a
                                                                id="{{ $linkId }}"
                                                                href="{{ $receiptAssetUrl }}"
                                                                download
                                                                class="{{ VC::BT_SM }} btn-secondary btn-icon rounded-pill"
                                                                target="_blank"
                                                                data-url="{{ $receiptAssetUrl }}"
                                                                data-guard-msg="{{ $guardMsg }}"
                                                            >
                                                                <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const link = document.getElementById('{{ $linkId }}');
                                                                if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                                link.setAttribute('data-listener-active', 'true');
                                                                link.addEventListener('click', e => {
                                                                    try {
                                                                        const url = link.getAttribute('data-url') ?? '#';
                                                                        if (url !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bsLink && window.bootstrap) {
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
                                                    <td>
                                                        {{__('No Receipt')}}
                                                    </td>
                                                @endif
                                            </td>
                                            <td>{{$canFormatDate ? (!empty($payment->date) ? $user->dateFormat($payment->date) : __('No date for payment available')) : __('Failed to format payment date')}}</td>
                                            <td>{{$canFormatPrice ? (!empty($payment->amount) ? $user?->priceFormat($payment->amount) : __('No price amount for payment available')) : __('Failed to format payment amount price')}}</td>
                                            <td>{{!empty($payment->payment_type) ? $payment->payment_type : __('No payment type available')}}</td>
                                            <td>{{!empty($payment->bankAccount) && !empty($payment->bankAccount->bank_name) && !empty($payment->bankAccount->holder_name) ? $payment->bankAccount->bank_name.' '.$payment->bankAccount->holder_name: __('No information about bank and holder name')}}</td>
                                            <td>{{!empty($payment->reference) ? $payment->reference: __('No reference available for payment')}}</td>
                                            <td>{{!empty($payment->description)?$payment->description: __('No description available for payment')}}</td>
                                            @if(!$invoiceUserAv || !$userPlanAv)
                                                <td>
                                                    {{ __('Failed to access plans data') }}
                                                </td>
                                            @else
                                                @if($user_plan->storage_limit <= $invoice_user->storage_limit)
                                                    <td>
                                                        <small class="text-danger font-bold">{{__('Your plan storage limit is over')}}</small>
                                                    </td>
                                                @else
                                                    <td>
                                                        @if(!empty($payment->receipt))
                                                            @php
                                                                $receiptUrl          = $path . '/' . $payment->receipt;
                                                                $linkId              = 'payment-receipt-link-' . $payment->id;
                                                                $guardMsg            = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::INV,
                                                                    'payment_receipt_view_unavailable'
                                                                ) ?? 'Receipt view route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                                id="{{ $linkId }}"
                                                                href="{{ $receiptUrl }}"
                                                                target="_blank"
                                                                class="{{ VC::BT_SM }} btn-secondary btn-icon rounded-pill"
                                                                data-url="{{ $receiptUrl }}"
                                                                data-guard-msg="{{ $guardMsg }}"
                                                            >
                                                                <i class="{{ VC::TI_FL }}"></i>{{ __('Receipt') }}
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        const link = document.getElementById('{{ $linkId }}');
                                                                        if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                                        link.setAttribute('data-listener-active', 'true');
                                                                        link.addEventListener('click', event => {
                                                                            try {
                                                                                const url = link.getAttribute('data-url') || '#';
                                                                                if (url !== '#') return;
                                                                                event.preventDefault();
                                                                                const msg           = link.getAttribute('data-guard-msg') || '# ERROR';
                                                                                const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                                let container       = document.getElementById('toast-container');
                                                                                if (!container) {
                                                                                    container       = document.createElement('div');
                                                                                    container.id    = 'toast-container';
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bsLink && window.bootstrap) {
                                                                                    const toastEl      = document.createElement('div');
                                                                                    toastEl.className  = 'toast';
                                                                                    toastEl.setAttribute('role','alert');
                                                                                    toastEl.setAttribute('aria-live','assertive');
                                                                                    toastEl.setAttribute('aria-atomic','true');
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
                                                                            } catch (e) {}
                                                                        });
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        @elseif(!empty($payment->add_receipt))
                                                            @php
                                                                $addReceiptFilename = $payment->add_receipt;
                                                                $receiptUrl         = $addReceiptFilename
                                                                    ? asset(Storage::url("uploads/payment/{$addReceiptFilename}"))
                                                                    : '#';
                                                                $linkId             = 'payment-add-receipt-view-' . $payment->id;
                                                                $guardMsg           = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::INV,
                                                                    'payment_add_receipt_view_unavailable'
                                                                ) ?? 'Receipt view route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                                id="{{ $linkId }}"
                                                                href="{{ $receiptUrl }}"
                                                                target="_blank"
                                                                class=""
                                                                data-url="{{ $receiptUrl }}"
                                                                data-guard-msg="{{ $guardMsg }}"
                                                                {{ $receiptUrl === '#' ? 'aria-disabled="true"' : '' }}
                                                            >
                                                                <i class="{{ VC::TI_FL }}"></i>{{ __('Receipt') }}
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        const link = document.getElementById('{{ $linkId }}');
                                                                        if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                                        link.setAttribute('data-listener-active', 'true');
                                                                        link.addEventListener('click', event => {
                                                                            try {
                                                                                const url = link.getAttribute('data-url') || '#';
                                                                                if (url !== '#') return;
                                                                                event.preventDefault();
                                                                                const msg           = link.getAttribute('data-guard-msg') || '# ERROR';
                                                                                const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                                let container       = document.getElementById('toast-container');
                                                                                if (!container) {
                                                                                    container       = document.createElement('div');
                                                                                    container.id    = 'toast-container';
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bsLink && window.bootstrap) {
                                                                                    const toastEl      = document.createElement('div');
                                                                                    toastEl.className  = 'toast';
                                                                                    toastEl.setAttribute('role','alert');
                                                                                    toastEl.setAttribute('aria-live','assertive');
                                                                                    toastEl.setAttribute('aria-atomic','true');
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
                                                                            } catch (e) {}
                                                                        });
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        @else --
                                                        @endif
                                                    </td>
                                                @endif
                                            @endif
                                            <td>{{!empty($payment->order_id)?$payment->order_id:__('No order identificator available for payment')}}</td>
                                            @can('delete invoice product')
                                                @php
                                                    $destroyRouteName        = ViewsConstants::INV . '.payment.destroy';
                                                    $destroyUrl              = Route::has($destroyRouteName)
                                                        ? route($destroyRouteName, [$invoice->id, $payment->id])
                                                        : '#';
                                                    $formId                  = 'delete-form-' . $payment->id;
                                                    $linkId                  = 'delete-payment-link-' . $payment->id;
                                                    $guardMsg                = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::INV,
                                                        'payment_destroy_route_unavailable'
                                                    ) ?? 'Payment delete route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <td>
                                                    <div class="{{ VC::ACT_BTN_DNG }} {{ VC::MS2 }}">
                                                        {!! Collective\Html\FormFacade::open([
                                                            'method' => 'post',
                                                            'id'     => $formId,
                                                            'route'  => [ViewsConstants::INV . '.payment.destroy', $invoice->id, $payment->id]
                                                        ]) !!}
                                                            <a
                                                                id="{{ $linkId }}"
                                                                href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-url="{{ $destroyUrl }}"
                                                                data-guard-msg="{{ $guardMsg }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                </td>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            const link = document.getElementById('{{ $linkId }}');
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
                                                                    link.setAttribute('data-failed-route', 'true');
                                                                } catch (e) {}
                                                            });
                                                        })();
                                                    </script>
                                                @endpush
                                            @endcan
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="{{ (Gate::check('delete invoice product') ? '10' : '9') }}" class="text-center text-dark"><p>{{__('No Data Found for Payments')}}</p></td>
                                    </tr>
                                @endif
                                @if(Utility::isFilled($invoice->bankPayments) ?? [])
                                    @foreach($invoice->bankPayments as $key =>$bankPayment)
                                        <tr>
                                            <td>-</td>
                                            <td>{{$canFormatDate ? (!empty($bankPayment->date) ? $user->dateFormat($bankPayment->date) : __('No date for bank payment available')) : __('Failed to format bank payment date')}}</td>
                                            <td>{{$canFormatPrice ? (!empty($bankPayment->amount) ? $user?->priceFormat($bankPayment->amount) : __('No price amount for bank payment available')) : __('Failed to format bank payment amount price')}}</td>
                                            <td>{{__('Bank Transfer')}}<br>
                                            </td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            @if(!$invoiceUserAv || !$userPlanAv)
                                                <td>
                                                    {{ __('Failed to access plans data') }}
                                                </td>
                                            @else
                                                @if($user_plan->storage_limit <= $invoice_user->storage_limit)
                                                    <td>
                                                        <small class="text-danger font-bold">{{__('Your plan storage limit is over')}}</small>
                                                    </td>
                                                @else
                                                    <td>
                                                        @if(!empty($bankPayment->receipt))
                                                            @php
                                                                $receiptUrl               = $path . '/' . $bankPayment->receipt;
                                                                $receiptLinkId            = 'bankpayment-receipt-link-' . $bankPayment->id;
                                                                $receiptGuardMsg          = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::INV,
                                                                    'bankpayment_receipt_route_unavailable'
                                                                ) ?? 'Bank payment receipt route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                                id="{{ $receiptLinkId }}"
                                                                href="{{ $receiptUrl }}"
                                                                target="_blank"
                                                                class=""
                                                                data-url="{{ $receiptUrl }}"
                                                                data-guard-msg="{{ $receiptGuardMsg }}"
                                                            >
                                                                <i class="{{ VC::TI_FL }}"></i> {{ __('Receipt') }}
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        const link = document.getElementById('{{ $receiptLinkId }}');
                                                                        if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                                        link.setAttribute('data-listener-active', 'true');
                                                            
                                                                        link.addEventListener('click', event => {
                                                                            try {
                                                                                const url = link.getAttribute('data-url') || '#';
                                                                                if (url !== '#') return; // valid URL, follow through
                                                                                event.preventDefault();
                                                                                const msg           = link.getAttribute('data-guard-msg') || '# ERROR';
                                                                                const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                                let container       = document.getElementById('toast-container');
                                                                                if (!container) {
                                                                                    container       = document.createElement('div');
                                                                                    container.id    = 'toast-container';
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bsLink && window.bootstrap) {
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
                                                                            } catch (e) {}
                                                                        });
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        @endif
                                                    </td>
                                                @endif
                                            <td>{{!empty($bankPayment->order_id)?$bankPayment->order_id:__('Failed to retrieve Bank payment order identifier')}}</td>
                                            @can('delete invoice product')
                                                <td>
                                                    @if($bankPayment->status == 'Pending')
                                                        <div class="{{ VC::ACT_BTN_WRN }}">
                                                            @php
                                                                $actionPath              = ViewsConstants::INV . '/' . $bankPayment->id . '/action';
                                                                $actionUrl               = URL::to($actionPath) ?: '#';
                                                                $statusLinkId            = 'bankpayment-status-link-' . $bankPayment->id;
                                                                $statusGuardMsg          = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::INV,
                                                                    'payment_status_action_route_unavailable'
                                                                ) ?? 'Payment status action route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                                id="{{ $statusLinkId }}"
                                                                href="#"
                                                                data-url="{{ $actionUrl }}"
                                                                data-size="lg"
                                                                data-ajax-popup="true"
                                                                data-title="{{ __('Payment Status') }}"
                                                                class="{{ VC::BT_SM_CT }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Payment Status') }}"
                                                                data-guard-msg="{{ $statusGuardMsg }}"
                                                                {{ $actionUrl === '#' ? 'aria-disabled="true"' : '' }}
                                                            >
                                                                <i class="{{ VC::TI_CRT_WT }}"></i>
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        const link = document.getElementById('{{ $statusLinkId }}');
                                                                        if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                                        link.setAttribute('data-listener-active', 'true');
                                                            
                                                                        link.addEventListener('click', event => {
                                                                            try {
                                                                                const url = link.getAttribute('data-url') || '#';
                                                                                if (url !== '#') return; // valid URL, allow AJAX popup
                                                            
                                                                                event.preventDefault();
                                                                                const msg           = link.getAttribute('data-guard-msg') || '# ERROR';
                                                                                const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                                let container       = document.getElementById('toast-container');
                                                                                if (!container) {
                                                                                    container       = document.createElement('div');
                                                                                    container.id    = 'toast-container';
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bsLink && window.bootstrap) {
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
                                                                            } catch (e) {}
                                                                        });
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        </div>
                                                    @endif
                                                    <div class="{{ VC::ACT_BTN_DNG }} {{ VC::MS2 }}">
                                                        @php
                                                            $destroyRouteName           = ViewsConstants::INV . '.payment.destroy';
                                                            $destroyUrl                 = Route::has($destroyRouteName)
                                                                ? route($destroyRouteName, [$invoice->id, $bankPayment->id])
                                                                : '#';
                                                            $formId                     = 'delete-form-' . $bankPayment->id;
                                                            $linkId                     = 'delete-bankpayment-link-' . $bankPayment->id;
                                                            $destroyGuardMsg            = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::INV,
                                                                'payment_destroy_route_unavailable'
                                                            ) ?? 'Delete payment route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        {!! Collective\Html\FormFacade::open([
                                                            'method' => 'post',
                                                            'route'  => [ViewsConstants::INV . '.payment.destroy', $invoice->id, $bankPayment->id],
                                                            'id'     => $formId
                                                        ]) !!}
                                                            <a
                                                                id="{{ $linkId }}"
                                                                href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-url="{{ $destroyUrl }}"
                                                                data-guard-msg="{{ $destroyGuardMsg }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const link = document.getElementById('{{ $linkId }}');
                                                                    if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                                    link.setAttribute('data-listener-active', 'true');
                                                        
                                                                    link.addEventListener('click', event => {
                                                                        try {
                                                                            const url = link.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg           = link.getAttribute('data-guard-msg') || '# ERROR';
                                                                            const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                            let container       = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container       = document.createElement('div');
                                                                                container.id    = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bsLink && window.bootstrap) {
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
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                </td>
                                            @endcan
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="{{ (Gate::check('delete invoice product') ? '10' : '9') }}" class="text-center text-dark"><p>{{__('No Data Found for Bank Payments')}}</p></td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD }} card-body table-border-style">
                        <h5 class="d-inline-block {{ VC::MB5 }}">{{__('Credit Note Summary')}}</h5>
                        <div class="table-responsive">
                            <table class="{{ VC::TB }}">
                                <thead>
                                <tr>
                                    <th class="text-dark">{{__('Date')}}</th>
                                    <th class="text-dark">{{__('Amount')}}</th>
                                    <th class="text-dark">{{__('Description')}}</th>
                                    @if(Gate::check('edit credit note') || Gate::check('delete credit note'))
                                        <th class="text-dark">{{__('Action')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                @forelse($invoice->creditNote as $key =>$creditNote)
                                    <tr>
                                        <td>{{$canFormatDate ? ($user?->dateFormat($creditNote->date) : __('No date for credit note available')) : __('Failed to format credit note date')}}</td>
                                        <td>{{$canFormatPrice ? ($user?->priceFormat($creditNote->amount) : __('No price amount for credit note available')) : __('Failed to format credit note amount price')}}</td>
                                        <td>{{!empty($creditNote->description) ? $creditNote->description : __('No description for credit note available')}}</td>
                                        <td>
                                            @can('edit credit note')
                                                <div class="{{ VC::ACT_BTN_PRIM }} {{ VC::MS2 }}">
                                                    @php
                                                        $editCreditRouteName        = ViewsConstants::INV . '.edit.credit.note';
                                                        $editCreditUrl              = Route::has($editCreditRouteName)
                                                            ? route($editCreditRouteName, [$creditNote->invoice, $creditNote->id])
                                                            : '#';
                                                        $editCreditLinkId           = 'edit-credit-note-link-' . $creditNote->id;
                                                        $editCreditGuardMsg         = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::INV,
                                                            'edit_credit_note_route_unavailable'
                                                        ) ?? 'Edit Credit Note route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <a
                                                        id="{{ $editCreditLinkId }}"
                                                        href="{{ $editCreditUrl }}"
                                                        class="{{ VC::BT_SM_CT }}"
                                                        data-url="{{ $editCreditUrl }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Credit Note') }}"
                                                        data-guard-msg="{{ $editCreditGuardMsg }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        {{ $editCreditUrl === '#' ? 'aria-disabled="true"' : '' }}
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const link = document.getElementById('{{ $editCreditLinkId }}');
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
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
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
                                                                    } catch (e) {}
                                                                });
                                                            })();
                                                        </script>
                                                    @endpush
                                                </div>
                                            @endcan
                                            @can('delete credit note')
                                                @php
                                                    $deleteCreditRouteName           = ViewsConstants::INV . '.delete.credit.note';
                                                    $deleteCreditUrl                 = Route::has($deleteCreditRouteName)
                                                        ? route($deleteCreditRouteName, [$creditNote->invoice, $creditNote->id])
                                                        : '#';
                                                    $deleteCreditFormId              = 'delete-creditnote-form-' . $creditNote->id;
                                                    $deleteCreditLinkId              = 'delete-creditnote-link-' . $creditNote->id;
                                                    $deleteCreditGuardMsg            = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::INV,
                                                        'delete_credit_note_route_unavailable'
                                                    ) ?? 'Delete Credit Note route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_DNG }} {{ VC::MS2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method' => 'DELETE',
                                                        'route'  => [ViewsConstants::INV . '.delete.credit.note', $creditNote->invoice, $creditNote->id],
                                                        'id'     => $deleteCreditFormId
                                                    ]) !!}
                                                        <a
                                                            id="{{ $deleteCreditLinkId }}"
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-url="{{ $deleteCreditUrl }}"
                                                            data-guard-msg="{{ $deleteCreditGuardMsg }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                        >
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            const link = document.getElementById('{{ $deleteCreditLinkId }}');
                                                            if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                            link.setAttribute('data-listener-active', 'true');
                                                
                                                            link.addEventListener('click', event => {
                                                                try {
                                                                    const url = link.getAttribute('data-url') || '#';
                                                                    if (url !== '#') return;
                                                                    event.preventDefault();
                                                                    const msg           = link.getAttribute('data-guard-msg') || '# ERROR';
                                                                    const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                    let container       = document.getElementById('toast-container');
                                                                    if (!container) {
                                                                        container       = document.createElement('div');
                                                                        container.id    = 'toast-container';
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bsLink && window.bootstrap) {
                                                                        const toastEl      = document.createElement('div');
                                                                        toastEl.className  = 'toast';
                                                                        toastEl.setAttribute('role','alert');
                                                                        toastEl.setAttribute('aria-live','assertive');
                                                                        toastEl.setAttribute('aria-atomic','true');
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
                                                                } catch (e) {}
                                                            });
                                                        })();
                                                    </script>
                                                @endpush
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <p class="text-dark">{{__('No Data Found')}}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD }} card-body">
                        <div class="text-center">
                            <h3>{{__('No invoice available')}}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
