@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Crypt, Route, URL};
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
    <li class="breadcrumb-item">{{ $user?->invoiceNumberFormat($invoice->invoice_id) }}</li>
@endsection
@php
    $settings = Utility::settings();
@endphp
@push(StacksConstants::ADM_CSS)
    <style>
        #card-element {
            border: 1px solid #a3afbb !important;
            border-radius: 10px !important;
            padding: 10px !important;
        }
    </style>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script async src="https://js.stripe.com/v3/"></script>
    <script async src="https://js.paystack.co/v1/inline.js"></script>
    <script async src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
    <script async src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar:  { stripe_unavailable: 'لا يمكن تحميل Stripe', paystack_unavailable: 'لا يمكن تحميل Paystack', flutterwave_unavailable: 'لا يمكن تحميل Flutterwave', razorpay_unavailable: 'لا يمكن تحميل Razorpay', link_copy_unavailable: 'فشل نسخ الرابط', shipping_toggle_unavailable: 'فشل تبديل الشحن' },
            da:  { stripe_unavailable: 'Kan ikke indlæse Stripe', paystack_unavailable: 'Kan ikke indlæse Paystack', flutterwave_unavailable: 'Kan ikke indlæse Flutterwave', razorpay_unavailable: 'Kan ikke indlæse Razorpay', link_copy_unavailable: 'Kan ikke kopiere link', shipping_toggle_unavailable: 'Skift af forsendelse mislykkedes' },
            de:  { stripe_unavailable: 'Stripe konnte nicht geladen werden', paystack_unavailable: 'Paystack konnte nicht geladen werden', flutterwave_unavailable: 'Flutterwave konnte nicht geladen werden', razorpay_unavailable: 'Razorpay konnte nicht geladen werden', link_copy_unavailable: 'Link konnte nicht kopiert werden', shipping_toggle_unavailable: 'Versandumschaltung fehlgeschlagen' },
            en:  { stripe_unavailable: 'Cannot load Stripe', paystack_unavailable: 'Cannot load Paystack', flutterwave_unavailable: 'Cannot load Flutterwave', razorpay_unavailable: 'Cannot load Razorpay', link_copy_unavailable: 'Cannot copy link', shipping_toggle_unavailable: 'Shipping toggle failed' },
            es:  { stripe_unavailable: 'No se puede cargar Stripe', paystack_unavailable: 'No se puede cargar Paystack', flutterwave_unavailable: 'No se puede cargar Flutterwave', razorpay_unavailable: 'No se puede cargar Razorpay', link_copy_unavailable: 'No se puede copiar el enlace', shipping_toggle_unavailable: 'Error al alternar envío' },
            fr:  { stripe_unavailable: 'Impossible de charger Stripe', paystack_unavailable: 'Impossible de charger Paystack', flutterwave_unavailable: 'Impossible de charger Flutterwave', razorpay_unavailable: 'Impossible de charger Razorpay', link_copy_unavailable: 'Impossible de copier le lien', shipping_toggle_unavailable: 'Échec du basculement de livraison' },
            he:  { stripe_unavailable: 'לא ניתן לטעון Stripe', paystack_unavailable: 'לא ניתן לטעון Paystack', flutterwave_unavailable: 'לא ניתן לטעון Flutterwave', razorpay_unavailable: 'לא ניתן לטעון Razorpay', link_copy_unavailable: 'העתקת הקישור נכשלה', shipping_toggle_unavailable: 'החלפת שילוח נכשלה' },
            it:  { stripe_unavailable: 'Impossibile caricare Stripe', paystack_unavailable: 'Impossibile caricare Paystack', flutterwave_unavailable: 'Impossibile caricare Flutterwave', razorpay_unavailable: 'Impossibile caricare Razorpay', link_copy_unavailable: 'Impossibile copiare il link', shipping_toggle_unavailable: 'Errore commutazione spedizione' },
            ja:  { stripe_unavailable: 'Stripeを読み込めません', paystack_unavailable: 'Paystackを読み込めません', flutterwave_unavailable: 'Flutterwaveを読み込めません', razorpay_unavailable: 'Razorpayを読み込めません', link_copy_unavailable: 'リンクをコピーできません', shipping_toggle_unavailable: '配送切り替えに失敗しました' },
            nl:  { stripe_unavailable: 'Kan Stripe niet laden', paystack_unavailable: 'Kan Paystack niet laden', flutterwave_unavailable: 'Kan Flutterwave niet laden', razorpay_unavailable: 'Kan Razorpay niet laden', link_copy_unavailable: 'Kan link niet kopiëren', shipping_toggle_unavailable: 'Verzending wisselen mislukt' },
            pl:  { stripe_unavailable: 'Nie można załadować Stripe', paystack_unavailable: 'Nie można załadować Paystack', flutterwave_unavailable: 'Nie można załadować Flutterwave', razorpay_unavailable: 'Nie można załadować Razorpay', link_copy_unavailable: 'Nie można skopiować linku', shipping_toggle_unavailable: 'Nie udało się zmienić wysyłki' },
            pt:  { stripe_unavailable: 'Não foi possível carregar Stripe', paystack_unavailable: 'Não foi possível carregar Paystack', flutterwave_unavailable: 'Não foi possível carregar Flutterwave', razorpay_unavailable: 'Não foi possível carregar Razorpay', link_copy_unavailable: 'Não foi possível copiar o link', shipping_toggle_unavailable: 'Falha ao alternar frete' },
            'pt-br': { stripe_unavailable: 'Não foi possível carregar Stripe', paystack_unavailable: 'Não foi possível carregar Paystack', flutterwave_unavailable: 'Não foi possível carregar Flutterwave', razorpay_unavailable: 'Não foi possível carregar Razorpay', link_copy_unavailable: 'Não foi possível copiar o link', shipping_toggle_unavailable: 'Falha ao alternar frete' },
            ru:  { stripe_unavailable: 'Не удалось загрузить Stripe', paystack_unavailable: 'Не удалось загрузить Paystack', flutterwave_unavailable: 'Не удалось загрузить Flutterwave', razorpay_unavailable: 'Не удалось загрузить Razorpay', link_copy_unavailable: 'Не удалось скопировать ссылку', shipping_toggle_unavailable: 'Не удалось переключить доставку' },
            tr:  { stripe_unavailable: 'Stripe yüklenemiyor', paystack_unavailable: 'Paystack yüklenemiyor', flutterwave_unavailable: 'Flutterwave yüklenemiyor', razorpay_unavailable: 'Razorpay yüklenemiyor', link_copy_unavailable: 'Bağlantı kopyalanamadı', shipping_toggle_unavailable: 'Gönderim geçişi başarısız' },
            zh:  { stripe_unavailable: '无法加载 Stripe', paystack_unavailable: '无法加载 Paystack', flutterwave_unavailable: '无法加载 Flutterwave', razorpay_unavailable: '无法加载 Razorpay', link_copy_unavailable: '无法复制链接', shipping_toggle_unavailable: '运送切换失败' }
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

            try {
                if (typeof $ === 'undefined') {
                    console.error('jQuery is required');
                    return;
                }

                @if($invoice->getDue() > 0
                    && !empty($company_payment_setting)
                    && $company_payment_setting['is_stripe_enabled'] === 'on'
                    && $company_payment_setting['stripe_key']
                    && $company_payment_setting['stripe_secret']
                )
                    try {
                        const stripeKey = '{{ $company_payment_setting['stripe_key'] }}';
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
                                        key: '{{ $company_payment_setting['paystack_public_key'] }}',
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
                                        PBFPubKey: '{{ $company_payment_setting['flutterwave_public_key'] }}',
                                        customer_email: '{{ Auth::user()->email }}',
                                        amount: res.total_price,
                                        currency: '{{ Utility::getValByName("site_currency") }}',
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
                                        key: '{{ $company_payment_setting['razorpay_public_key'] }}',
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
                console.error('Initialization failed', e);
            }
        })();
    </script>
@endpush
@section('content')
    @can('send invoice')
        @if($invoice->status!=4)
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
                                        {{ __('Created on ') }}{{ $user?->dateFormat($invoice->issue_date) }}
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
                                            {{ __('Sent on') }} {{ $user?->dateFormat($invoice->send_date) }}
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
        @if($invoice->status!=0)
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
                                    <h4 class="invoice-number">{{ $user?->invoiceNumberFormat($invoice->invoice_id) }}</h4>
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
                                                {{$user?->dateFormat($invoice->issue_date)}}<br><br>
                                            </small>
                                        </div>
                                        <div>
                                            <small>
                                                <strong>{{__('Due Date')}} :</strong><br>
                                                {{$user?->dateFormat($invoice->due_date)}}<br><br>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::RW }}">
    
                                    <div class="col">
                                        <small class="font-style">
                                            <strong>{{__('Billed To')}} :</strong><br>
                                            @if(!empty($customer->billing_name))
                                                {{!empty($customer->billing_name)?$customer->billing_name:''}}<br>
                                                {{!empty($customer->billing_address)?$customer->billing_address:''}}<br>
                                                {{!empty($customer->billing_city)?$customer->billing_city:'' .', '}}<br>
                                                {{!empty($customer->billing_state)?$customer->billing_state:'',', '}},
                                                {{!empty($customer->billing_zip)?$customer->billing_zip:''}}<br>
                                                {{!empty($customer->billing_country)?$customer->billing_country:''}}<br>
                                                {{!empty($customer->billing_phone)?$customer->billing_phone:''}}<br>
                                                @if($settings['vat_gst_number_switch'] == 'on')
                                                    <strong>{{__('Tax Number ')}} : </strong>{{!empty($customer->tax_number)?$customer->tax_number:''}}
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
                                            {{!empty($customer->shipping_name)?$customer->shipping_name:''}}<br>
                                            {{!empty($customer->shipping_address)?$customer->shipping_address:''}}<br>
                                            {{!empty($customer->shipping_city)?$customer->shipping_city:'' . ', '}}<br>
                                            {{!empty($customer->shipping_state)?$customer->shipping_state:'' .', '}},
                                            {{!empty($customer->shipping_zip)?$customer->shipping_zip:''}}<br>
                                            {{!empty($customer->shipping_country)?$customer->shipping_country:''}}<br>
                                            {{!empty($customer->shipping_phone)?$customer->shipping_phone:''}}<br>
                                            @else
                                                -
                                            @endif
                                        </small>
                                    </div>
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
                                            {!! DNS2D::getBarcodeHTML($copyUrl, "QRCODE", 2, 2) !!}
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
                                        @endif
                                    </small>
                                </div>
    
                                @if(!empty($customFields) && count($invoice->customField)>0)
                                    @foreach($customFields as $field)
                                        <div class="col text-md-right">
                                            <small>
                                                <strong>{{$field->name}} :</strong><br>
                                                {{!empty($invoice->customField)?$invoice->customField[$field->id]:'-'}}
                                                <br><br>
                                            </small>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="{{ VC::RW }} {{ VC::MT4 }}">
                                <div class="{{ VC::CM12 }}">
                                    <div class="font-weight-bold">{{__('Product Summary')}}</div>
                                    <small>{{__('All items here cannot be deleted.')}}</small>
                                    <div class="table-responsive {{ VC::MT2 }}">
                                        <table class="{{ VC::TB }} {{ VC::MB0 }} table-striped">
                                            <tr>
                                                <th data-width="40" class="text-dark">#</th>
                                                <th class="text-dark">{{__('Product')}}</th>
                                                <th class="text-dark">{{__('Quantity')}}</th>
                                                <th class="text-dark">{{__('Rate')}}</th>
                                                <th class="text-dark">{{__('Discount')}}</th>
                                                <th class="text-dark">{{__('Tax')}}</th>
                                                <th class="text-dark">{{__('Description')}}</th>
                                                <th class="{{ VC::TXT_END }} text-dark" width="12%">{{__('Price')}}<br>
                                                    <small class="text-danger font-weight-bold">{{__('after tax & discount')}}</small>
                                                </th>
                                            </tr>
                                            @php
                                                $totalQuantity=0;
                                                $totalRate=0;
                                                $totalTaxPrice=0;
                                                $totalDiscount=0;
                                                $taxesData=[];
                                            @endphp
                                            @foreach($iteams as $key =>$iteam)
                                                @if(!empty($iteam->tax))
                                                    @php
                                                        $taxes=Utility::tax($iteam->tax);
                                                        $totalQuantity+=$iteam->quantity;
                                                        $totalRate+=$iteam->price;
                                                        $totalDiscount+=$iteam->discount;
                                                        foreach($taxes as $taxe){
                                                            $taxDataPrice=Utility::taxRate($taxe->rate,$iteam->price,$iteam->quantity,$iteam->discount);
                                                            if (array_key_exists($taxe->name,$taxesData))
                                                            {
                                                                $taxesData[$taxe->name] = $taxesData[$taxe->name]+$taxDataPrice;
                                                            }
                                                            else
                                                            {
                                                                $taxesData[$taxe->name] = $taxDataPrice;
                                                            }
                                                        }
                                                    @endphp
                                                @endif
                                                <tr>
                                                    <td>{{$key+1}}</td>
                                                    @php
                                                        $productName = $iteam->product;
                                                        $unit = $productName->unit_id;
                                                        $unitName = App\Models\ProductServiceUnit::find($unit);
                                                    @endphp
                                                    <td>{{!empty($productName)?$productName->name:''}}</td>
                                                    <td>{{$iteam->quantity . ' (' . $unitName->name . ')'}}</td>
                                                    <td>{{$user?->priceFormat($iteam->price)}}</td>
                                                    <td>{{$user?->priceFormat($iteam->discount)}}</td>
    
                                                    <td>
                                                        @if(!empty($iteam->tax))
                                                            <table>
                                                                @php
                                                                    $totalTaxRate = 0;
                                                                @endphp
                                                                @foreach($taxes as $tax)
                                                                    @php
                                                                        $taxPrice=Utility::taxRate($tax->rate,$iteam->price,$iteam->quantity,$iteam->discount) ;
                                                                        $totalTaxPrice+=$taxPrice;
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{$tax->name .' ('.$tax->rate .'%)'}}</td>
                                                                        <td>{{$user?->priceFormat($taxPrice)}}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
    
                                                    <td>{{!empty($iteam->description)?$iteam->description:'-'}}</td>
                                                    <td class="{{ VC::TXT_END }}">{{$user?->priceFormat(($iteam->price * $iteam->quantity - $iteam->discount) + $totalTaxPrice)}}</td>
                                                </tr>
                                            @endforeach
                                            <tfoot>
                                            <tr>
                                                <td></td>
                                                <td><b>{{__('Total')}}</b></td>
                                                <td><b>{{$totalQuantity}}</b></td>
                                                <td><b>{{$user?->priceFormat($totalRate)}}</b></td>
                                                <td><b>{{$user?->priceFormat($totalDiscount)}}</b></td>
                                                <td><b>{{$user?->priceFormat($totalTaxPrice)}}</b></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="{{ VC::TXT_END }}"><b>{{__('Sub Total')}}</b></td>
                                                <td class="{{ VC::TXT_END }}">{{$user?->priceFormat($invoice->getSubTotal())}}</td>
                                            </tr>
    
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="{{ VC::TXT_END }}"><b>{{__('Discount')}}</b></td>
                                                    <td class="{{ VC::TXT_END }}">{{$user?->priceFormat($invoice->getTotalDiscount())}}</td>
                                                </tr>
    
                                            @if(!empty($taxesData))
                                                @foreach($taxesData as $taxName => $taxPrice)
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="{{ VC::TXT_END }}"><b>{{$taxName}}</b></td>
                                                        <td class="{{ VC::TXT_END }}">{{ \Auth::user()->priceFormat($taxPrice) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="blue-text {{ VC::TXT_END }}"><b>{{__('Total')}}</b></td>
                                                <td class="blue-text {{ VC::TXT_END }}">{{$user?->priceFormat($invoice->getTotal())}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="{{ VC::TXT_END }}"><b>{{__('Paid')}}</b></td>
                                                <td class="{{ VC::TXT_END }}">{{$user?->priceFormat(($invoice->getTotal()-$invoice->getDue())-($invoice->invoiceTotalCreditNote()))}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="{{ VC::TXT_END }}"><b>{{__('Credit Note')}}</b></td>
                                                <td class="{{ VC::TXT_END }}">{{$user?->priceFormat(($invoice->invoiceTotalCreditNote()))}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="{{ VC::TXT_END }}"><b>{{__('Due')}}</b></td>
                                                <td class="{{ VC::TXT_END }}">{{$user?->priceFormat($invoice->getDue())}}</td>
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
                    @if($user_plan->storage_limit <= $invoice_user->storage_limit)
                        <small class="text-danger font-bold">{{__('Your plan storage limit is over , so you can not see customer uploaded payment receipt')}}</small><br>
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
    
                            @if(!empty($invoice->payments) && $invoice->bankPayments)
                                @php
                                    $path = Utility::getFile('uploads/order');
                                @endphp
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
                                                -
                                            @endif
                                        </td>
                                        <td>{{$user?->dateFormat($payment->date)}}</td>
                                        <td>{{$user?->priceFormat($payment->amount)}}</td>
                                        <td>{{$payment->payment_type}}</td>
                                        <td>{{!empty($payment->bankAccount)?$payment->bankAccount->bank_name.' '.$payment->bankAccount->holder_name:'--'}}</td>
                                        <td>{{!empty($payment->reference)?$payment->reference:'--'}}</td>
                                        <td>{{!empty($payment->description)?$payment->description:'--'}}</td>
                                        @if($user_plan->storage_limit <= $invoice_user->storage_limit)
                                            <td>
                                                --
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
                                        <td>{{!empty($payment->order_id)?$payment->order_id:'--'}}</td>
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
                                {{--  start for bank transfer--}}
                                @foreach($invoice->bankPayments as $key =>$bankPayment)
                                    <tr>
                                        <td>-</td>
                                        <td>{{$user?->dateFormat($bankPayment->date)}}</td>
                                        <td>{{$user?->priceFormat($bankPayment->amount)}}</td>
                                        <td>{{__('Bank Transfer')}}<br>
                                        </td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        @if($user_plan->storage_limit <= $invoice_user->storage_limit)
                                            <td>
                                                ---
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
                                        <td>{{!empty($bankPayment->order_id)?$bankPayment->order_id:'--'}}</td>
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
                                {{--  end for bank transfer--}}
                            @else
                                <tr>
                                    <td colspan="{{ (Gate::check('delete invoice product') ? '10' : '9') }}" class="text-center text-dark"><p>{{__('No Data Found')}}</p></td>
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
                                    <td>{{$user?->dateFormat($creditNote->date)}}</td>
                                    <td>{{$user?->priceFormat($creditNote->amount)}}</td>
                                    <td>{{$creditNote->description}}</td>
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
@endsection
