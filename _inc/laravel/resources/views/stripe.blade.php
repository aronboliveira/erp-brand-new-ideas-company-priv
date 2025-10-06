@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        PlansConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Plan,Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Crypt, Route, Storage};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar: {
            stripe_unavailable: "تعذّر تهيئة Stripe للدفع",
            paystack_unavailable: "تعذّر تهيئة Paystack للدفع",
            flutterwave_unavailable: "تعذّر تهيئة Flutterwave للدفع",
            razorpay_unavailable: "تعذّر تهيئة Razorpay للدفع",
            payfast_unavailable: "تعذّر الحصول على حالة Payfast",
            coupon_apply_unavailable: "تعذّر تطبيق القسيمة",
            scrollspy_unavailable: "تعذّر تهيئة ScrollSpy"
            },
            da: {
            stripe_unavailable: "Kunne ikke initialisere Stripe-betaling",
            paystack_unavailable: "Kunne ikke initialisere Paystack-betaling",
            flutterwave_unavailable: "Kunne ikke initialisere Flutterwave-betaling",
            razorpay_unavailable: "Kunne ikke initialisere Razorpay-betaling",
            payfast_unavailable: "Kunne ikke hente Payfast-status",
            coupon_apply_unavailable: "Kunne ikke anvende kuponen",
            scrollspy_unavailable: "Kunne ikke initialisere ScrollSpy"
            },
            de: {
            stripe_unavailable: "Stripe-Zahlung konnte nicht initialisiert werden",
            paystack_unavailable: "Paystack-Zahlung konnte nicht initialisiert werden",
            flutterwave_unavailable: "Flutterwave-Zahlung konnte nicht initialisiert werden",
            razorpay_unavailable: "Razorpay-Zahlung konnte nicht initialisiert werden",
            payfast_unavailable: "Payfast-Status konnte nicht abgerufen werden",
            coupon_apply_unavailable: "Gutschein konnte nicht angewendet werden",
            scrollspy_unavailable: "ScrollSpy konnte nicht initialisiert werden"
            },
            en: {
            stripe_unavailable: "Unable to initialize Stripe payment",
            paystack_unavailable: "Unable to initialize Paystack payment",
            flutterwave_unavailable: "Unable to initialize Flutterwave payment",
            razorpay_unavailable: "Unable to initialize Razorpay payment",
            payfast_unavailable: "Unable to fetch Payfast status",
            coupon_apply_unavailable: "Unable to apply coupon",
            scrollspy_unavailable: "Unable to initialize ScrollSpy"
            },
            es: {
            stripe_unavailable: "No se pudo inicializar el pago con Stripe",
            paystack_unavailable: "No se pudo inicializar el pago con Paystack",
            flutterwave_unavailable: "No se pudo inicializar el pago con Flutterwave",
            razorpay_unavailable: "No se pudo inicializar el pago con Razorpay",
            payfast_unavailable: "No se pudo obtener el estado de Payfast",
            coupon_apply_unavailable: "No se pudo aplicar el cupón",
            scrollspy_unavailable: "No se pudo inicializar ScrollSpy"
            },
            fr: {
            stripe_unavailable: "Impossible d’initialiser le paiement Stripe",
            paystack_unavailable: "Impossible d’initialiser le paiement Paystack",
            flutterwave_unavailable: "Impossible d’initialiser le paiement Flutterwave",
            razorpay_unavailable: "Impossible d’initialiser le paiement Razorpay",
            payfast_unavailable: "Impossible de récupérer l’état Payfast",
            coupon_apply_unavailable: "Impossible d’appliquer le coupon",
            scrollspy_unavailable: "Impossible d’initialiser ScrollSpy"
            },
            he: {
            stripe_unavailable: "לא ניתן לאתחל תשלום Stripe",
            paystack_unavailable: "לא ניתן לאתחל תשלום Paystack",
            flutterwave_unavailable: "לא ניתן לאתחל תשלום Flutterwave",
            razorpay_unavailable: "לא ניתן לאתחל תשלום Razorpay",
            payfast_unavailable: "לא ניתן לקבל מצב Payfast",
            coupon_apply_unavailable: "לא ניתן להחיל קופון",
            scrollspy_unavailable: "לא ניתן לאתחל ScrollSpy"
            },
            it: {
            stripe_unavailable: "Impossibile inizializzare il pagamento Stripe",
            paystack_unavailable: "Impossibile inizializzare il pagamento Paystack",
            flutterwave_unavailable: "Impossibile inizializzare il pagamento Flutterwave",
            razorpay_unavailable: "Impossibile inizializzare il pagamento Razorpay",
            payfast_unavailable: "Impossibile recuperare lo stato di Payfast",
            coupon_apply_unavailable: "Impossibile applicare il coupon",
            scrollspy_unavailable: "Impossibile inizializzare ScrollSpy"
            },
            ja: {
            stripe_unavailable: "Stripe の決済を初期化できませんでした",
            paystack_unavailable: "Paystack の決済を初期化できませんでした",
            flutterwave_unavailable: "Flutterwave の決済を初期化できませんでした",
            razorpay_unavailable: "Razorpay の決済を初期化できませんでした",
            payfast_unavailable: "Payfast ステータスを取得できませんでした",
            coupon_apply_unavailable: "クーポンを適用できませんでした",
            scrollspy_unavailable: "ScrollSpy を初期化できませんでした"
            },
            nl: {
            stripe_unavailable: "Kan Stripe-betaling niet initialiseren",
            paystack_unavailable: "Kan Paystack-betaling niet initialiseren",
            flutterwave_unavailable: "Kan Flutterwave-betaling niet initialiseren",
            razorpay_unavailable: "Kan Razorpay-betaling niet initialiseren",
            payfast_unavailable: "Kan Payfast-status niet ophalen",
            coupon_apply_unavailable: "Kan coupon niet toepassen",
            scrollspy_unavailable: "Kan ScrollSpy niet initialiseren"
            },
            pl: {
            stripe_unavailable: "Nie można zainicjować płatności Stripe",
            paystack_unavailable: "Nie można zainicjować płatności Paystack",
            flutterwave_unavailable: "Nie można zainicjować płatności Flutterwave",
            razorpay_unavailable: "Nie można zainicjować płatności Razorpay",
            payfast_unavailable: "Nie można pobrać statusu Payfast",
            coupon_apply_unavailable: "Nie można zastosować kuponu",
            scrollspy_unavailable: "Nie można zainicjować ScrollSpy"
            },
            pt: {
            stripe_unavailable: "Não foi possível iniciar o pagamento Stripe",
            paystack_unavailable: "Não foi possível iniciar o pagamento Paystack",
            flutterwave_unavailable: "Não foi possível iniciar o pagamento Flutterwave",
            razorpay_unavailable: "Não foi possível iniciar o pagamento Razorpay",
            payfast_unavailable: "Não foi possível obter o status do Payfast",
            coupon_apply_unavailable: "Não foi possível aplicar o cupom",
            scrollspy_unavailable: "Não foi possível iniciar o ScrollSpy"
            },
            "pt-br": {
            stripe_unavailable: "Não foi possível iniciar o pagamento Stripe",
            paystack_unavailable: "Não foi possível iniciar o pagamento Paystack",
            flutterwave_unavailable: "Não foi possível iniciar o pagamento Flutterwave",
            razorpay_unavailable: "Não foi possível iniciar o pagamento Razorpay",
            payfast_unavailable: "Não foi possível obter o status do Payfast",
            coupon_apply_unavailable: "Não foi possível aplicar o cupom",
            scrollspy_unavailable: "Não foi possível iniciar o ScrollSpy"
            },
            ru: {
            stripe_unavailable: "Не удалось инициализировать оплату Stripe",
            paystack_unavailable: "Не удалось инициализировать оплату Paystack",
            flutterwave_unavailable: "Не удалось инициализировать оплату Flutterwave",
            razorpay_unavailable: "Не удалось инициализировать оплату Razorpay",
            payfast_unavailable: "Не удалось получить статус Payfast",
            coupon_apply_unavailable: "Не удалось применить купон",
            scrollspy_unavailable: "Не удалось инициализировать ScrollSpy"
            },
            tr: {
            stripe_unavailable: "Stripe ödemesi başlatılamadı",
            paystack_unavailable: "Paystack ödemesi başlatılamadı",
            flutterwave_unavailable: "Flutterwave ödemesi başlatılamadı",
            razorpay_unavailable: "Razorpay ödemesi başlatılamadı",
            payfast_unavailable: "Payfast durumu alınamadı",
            coupon_apply_unavailable: "Kupon uygulanamadı",
            scrollspy_unavailable: "ScrollSpy başlatılamadı"
            },
            zh: {
            stripe_unavailable: "无法初始化 Stripe 支付",
            paystack_unavailable: "无法初始化 Paystack 支付",
            flutterwave_unavailable: "无法初始化 Flutterwave 支付",
            razorpay_unavailable: "无法初始化 Razorpay 支付",
            payfast_unavailable: "无法获取 Payfast 状态",
            coupon_apply_unavailable: "无法应用优惠券",
            scrollspy_unavailable: "无法初始化 ScrollSpy"
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
        (()=>{
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const BOUND = "data-np-bound";

            const getMsg = (el, msgKey) => {
            let msg = errFb;
            if (el.getAttribute("data-sv-localized")==="true" || el.getAttribute(dataClientLocalized)==="true") {
                msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
                let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
                .toLowerCase()
                .replace(/_/g,"-");
                lang = lang === "pt-br" ? lang : lang.slice(0,2);
                msg =
                window.translations?.[lang]?.[msgKey] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.en?.[msgKey] ||
                errFb;
                if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
                }
            }
            return msg;
            };

            const showDeferredError = (key, target, trigger="pointerup") => {
            const el = target || document.body;
            if (!el || el.getAttribute(BOUND)==="true") return;
            const onInteract = () => {
                const text = getMsg(document.body, key);
                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                if (hasBootstrap) {
                let toast = document.getElementById("np-error-toast");
                if (!toast) {
                    toast = document.createElement("div");
                    toast.id = "np-error-toast";
                    toast.className = "toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                    toast.setAttribute("role","alert");
                    toast.setAttribute("aria-live","assertive");
                    toast.setAttribute("aria-atomic","true");
                    toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">${text}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>`;
                    document.body.appendChild(toast);
                }
                new bootstrap.Toast(toast).show();
                } else {
                alert(text);
                }
            };
            el.addEventListener(trigger, onInteract, { once: true });
            el.setAttribute(BOUND, "true");
            const mo = new MutationObserver((_, obs) => {
                if (!document.body.contains(el)) { el.removeEventListener(trigger, onInteract); obs.disconnect(); }
            });
            mo.observe(document.body, { childList: true, subtree: true });
            };

            const routeInvalid = el => {
            const url = el?.getAttribute?.("data-url");
            const href = el?.action ?? el?.getAttribute?.("href");
            return ((!url || url === "#") && (!href || href === "#"));
            };

            if (typeof $ === "undefined") {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery failed to load");
            return;
            }

            @if($plan[PlansConstants::COL_PC] > 0.0 && $admin_payment_setting['is_stripe_enabled'] == 'on' && !empty($admin_payment_setting['stripe_key']) && !empty($admin_payment_setting['stripe_secret']))
            let stripe, elements, card;
            try {
                if (window.Stripe && document.getElementById("payment-form")) {
                stripe = Stripe('{{ $admin_payment_setting['stripe_key'] }}');
                elements = stripe.elements();
                const style = { base: { fontSize: "14px", color: "#32325d" } };
                card = elements.create("card", { style });
                const mountTarget = document.getElementById("card-element");
                if (mountTarget && !mountTarget.getAttribute(BOUND)) {
                    card.mount("#card-element");
                    mountTarget.setAttribute(BOUND, "true");
                }
                $(document).off("submit", "#payment-form").on("submit", "#payment-form", function(e){
                    e.preventDefault();
                    if (!stripe || !card) { showDeferredError("stripe_unavailable", this, "pointerup"); return; }
                    stripe.createToken(card).then(result=>{
                    if (result.error) {
                        try { $("#card-errors").html(result.error.message); show_toastr?.("Error", result.error.message, "error"); } catch {}
                    } else {
                        const form = document.getElementById("payment-form");
                        if (!form) return;
                        const hiddenInput = document.createElement("input");
                        hiddenInput.setAttribute("type","hidden");
                        hiddenInput.setAttribute("name","stripeToken");
                        hiddenInput.setAttribute("value", result.token.id);
                        form.appendChild(hiddenInput);
                        form.submit();
                    }
                    }).catch(()=>showDeferredError("stripe_unavailable", this, "pointerup"));
                });
                } else {
                const form = document.getElementById("payment-form");
                if (form) showDeferredError("stripe_unavailable", form, "pointerup");
                }
            } catch { showDeferredError("stripe_unavailable", document.getElementById("payment-form"), "pointerup"); }
            @endif

            $(document).off("click", ".apply-coupon").on("click", ".apply-coupon", function(){
            const $row = $(this).closest(".row");
            const coupon = $row.find(".coupon").val();
            if (!coupon) { showDeferredError("coupon_apply_unavailable", this, "click"); return; }
            $.ajax({
                url: '{{ route(VW::CPN . ".apply") }}',
                dataType: "json",
                data: {
                plan_id: '{{ Crypt::encrypt($plan->id) }}',
                coupon
                },
                success: data => {
                try {
                    if (data) {
                    $(".final-price").text(data.final_price ?? "");
                    $("#stripe_coupon, #paypal_coupon").val(coupon);
                    if (data.is_success === true) { show_toastr?.("success", data.message, "success"); }
                    else { show_toastr?.("Error", data.message, "error"); }
                    } else {
                    show_toastr?.("Error", "{{__('Coupon code required.')}}", "error");
                    }
                } catch {}
                },
                error: ()=>showDeferredError("coupon_apply_unavailable", this, "click")
            });
            });

            @if(isset($admin_payment_setting['is_paystack_enabled']) && $admin_payment_setting['is_paystack_enabled'] == 'on')
            $(document).off("click", "#pay_with_paystack").on("click", "#pay_with_paystack", function(){
                const btn = this;
                try {
                $('#paystack-payment-form').ajaxForm(function(res){
                    if (res.flag == 1) {
                    if (!window.PaystackPop) { showDeferredError("paystack_unavailable", btn, "pointerup"); return; }
                    const paystack_callback = "{{ url('/plan/paystack') }}";
                    const coupon_id = res.coupon;
                    const handler = PaystackPop.setup({
                        key: '{{ $admin_payment_setting['paystack_public_key'] }}',
                        email: res.email,
                        amount: res.total_price * 100,
                        currency: res.currency,
                        ref: 'pay_ref_id' + Math.floor((Math.random()*1e9)+1),
                        metadata: { custom_fields: [{ display_name: "Email", variable_name: "email", value: res.email }] },
                        callback: response => {
                        window.location.href = `${paystack_callback}/${response.reference}/{{ encrypt($plan->id) }}?coupon_id=${coupon_id}`;
                        },
                        onClose: ()=>{ try { alert('window closed'); } catch {} }
                    });
                    handler.openIframe();
                    } else if (res.flag == 2) {
                    // no-op by original code
                    } else {
                    try { show_toastr?.("Error", res.message, "msg"); } catch {}
                    }
                }).submit();
                } catch { showDeferredError("paystack_unavailable", btn, "pointerup"); }
            });
            @endif

            @if(isset($admin_payment_setting['is_flutterwave_enabled']) && $admin_payment_setting['is_flutterwave_enabled'] == 'on')
            $(document).off("click", "#pay_with_flutterwave").on("click", "#pay_with_flutterwave", function(){
                const btn = this;
                try {
                $('#flutterwave-payment-form').ajaxForm(function(res){
                    if (res.flag == 1) {
                    if (typeof getpaidSetup !== "function") { showDeferredError("flutterwave_unavailable", btn, "pointerup"); return; }
                    const coupon_id = res.coupon;
                    const API_publicKey = '{{ $admin_payment_setting['flutterwave_public_key'] }}';
                    const nowTim = "{{ date('d-m-Y-h-i-a') }}";
                    const flutter_callback = "{{ url('/plan/flutterwave') }}";
                    const x = getpaidSetup({
                        PBFPubKey: API_publicKey,
                        customer_email: '{{ Auth::user()->email }}',
                        amount: res.total_price,
                        currency: '{{ $admin_payment_setting['currency'] }}',
                        txref: nowTim + '__' + Math.floor((Math.random()*1e9)) + 'fluttpay_online-' + {{ date('Y-m-d') }},
                        meta: [{ metaname:"payment_id", metavalue:"id" }],
                        onclose: function(){},
                        callback: function(response){
                        const txref = response?.tx?.txRef;
                        if (response?.tx?.chargeResponseCode == "00" || response?.tx?.chargeResponseCode == "0") {
                            window.location.href = `${flutter_callback}/${txref}/{{ Crypt::encrypt($plan->id) }}?coupon_id=${coupon_id}`;
                        }
                        x.close();
                        }
                    });
                    } else if (res.flag == 2) {
                    // no-op by original
                    } else {
                    try { show_toastr?.("Error", res.message, "msg"); } catch {}
                    }
                }).submit();
                } catch { showDeferredError("flutterwave_unavailable", btn, "pointerup"); }
            });
            @endif

            @if(isset($admin_payment_setting['is_razorpay_enabled']) && $admin_payment_setting['is_razorpay_enabled'] == 'on')
            $(document).off("click", "#pay_with_razorpay").on("click", "#pay_with_razorpay", function(){
                const btn = this;
                try {
                $('#razorpay-payment-form').ajaxForm(function(res){
                    if (res.flag == 1) {
                    if (typeof Razorpay !== "function") { showDeferredError("razorpay_unavailable", btn, "pointerup"); return; }
                    const razorPay_callback = '{{ url('/plan/razorpay') }}';
                    const totalAmount = res.total_price * 100;
                    const coupon_id = res.coupon;
                    const options = {
                        key: "{{ $admin_payment_setting['razorpay_public_key'] }}",
                        amount: totalAmount,
                        name: "Plan",
                        currency: "{{ $admin_payment_setting['currency'] }}",
                        description: "",
                        handler: response => {
                        window.location.href = `${razorPay_callback}/${response.razorpay_payment_id}/{{ Crypt::encrypt($plan->id) }}?coupon_id=${coupon_id}`;
                        },
                        theme: { color: "#528FF0" }
                    };
                    const rzp1 = new Razorpay(options);
                    rzp1.open();
                    } else if (res.flag == 2) {
                    // no-op
                    } else {
                    try { show_toastr?.("Error", res.message, "msg"); } catch {}
                    }
                }).submit();
                } catch { showDeferredError("razorpay_unavailable", btn, "pointerup"); }
            });
            @endif

            @if ($admin_payment_setting['is_payfast_enabled'] == 'on' && !empty($admin_payment_setting['payfast_merchant_id']) && !empty($admin_payment_setting['payfast_merchant_key']))
            const get_payfast_status = (amount = 0, coupon = null) => {
                const planInput = document.getElementById("plan_id");
                const plan_id = planInput ? planInput.value : "";
                if (!plan_id) { return; }
                $.ajax({
                url: '{{ route("payfast.payment") }}',
                method: 'POST',
                data: { plan_id, coupon_amount: amount, coupon_code: coupon },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: data => {
                    if (data?.success === true) {
                    const holder = document.getElementById("get-payfast-inputs");
                    if (holder && !holder.querySelector("[data-np-payfast='true']")) {
                        const wrap = document.createElement("div");
                        wrap.setAttribute("data-np-payfast","true");
                        wrap.innerHTML = data.inputs || "";
                        holder.appendChild(wrap);
                    }
                    } else {
                    try { show_toastr?.("Error", data?.inputs || "", "error"); } catch {}
                    }
                },
                error: ()=>showDeferredError("payfast_unavailable", document.body, "pointerup")
                });
            };
            $(function(){ try { get_payfast_status(0, null); } catch { showDeferredError("payfast_unavailable", document.body, "pointerup"); } });
            @endif

            $(function(){
            try {
                if (window.bootstrap?.ScrollSpy) {
                const nav = document.getElementById("useradd-sidenav");
                if (nav && !document.body.getAttribute("data-np-scrollspy")) {
                    // eslint-disable-next-line no-new
                    new bootstrap.ScrollSpy(document.body, { target: "#useradd-sidenav", offset: 300 });
                    document.body.setAttribute("data-np-scrollspy","true");
                }
                } else {
                      if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("Bootstrap ScrollSpy not available");
                showDeferredError("scrollspy_unavailable", document.body, "click");
                }
            } catch { showDeferredError("scrollspy_unavailable", document.body, "click"); }
            });

            $(document).off("click", ".list-group-item").on("click", ".list-group-item", function(){
            const href = this?.href || "";
            if (!href) return;
            try {
                $('.list-group-item').filter(function(){ return this.href === href; }).parent().removeClass('text-primary');
            } catch {}
            });
        })();
    </script>
@endpush
@push(StacksConstants::ADM_CSS)
    <style>
        #card-element {
            border: 1px solid #a3afbb !important;
            border-radius: 10px !important;
            padding: 10px !important;
        }
    </style>
@endpush
@php
    $dir= asset(Storage::url('uploads/plan'));
    $dir_payment= asset(Storage::url('uploads/payments'));
@endphp
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Order Summary')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        $planIndexBaseName     = VW::PLN.'.index';
        $planIndexKebabName    = Str::kebab($planIndexBaseName);
        $planIndexResolvedName = Route::has($planIndexBaseName)
            ? $planIndexBaseName
            : (Route::has($planIndexKebabName) ? $planIndexKebabName : null);
        $planIndexUrl          = $planIndexResolvedName ? route($planIndexResolvedName) : '#';
        $planIndexGuardMsg     = Utility::fetchLinkMessage($lang, VW::PLN, 'index_plan_route_unavailable') ?? 'Index plan route is unavailable. Please contact technical support or your domain administrator.';
        $planIndexLinkId       = 'plan-index-breadcrumb-link';
    @endphp
    <li class="breadcrumb-item">
        <a href="{{ $planIndexUrl }}"
        id="{{ $planIndexLinkId }}"
        data-url="{{ $planIndexUrl }}"
        data-guard-msg="{{ $planIndexGuardMsg }}">
            {{ __('Plan') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                try {
                    const l = document.getElementById('{{ $planIndexLinkId }}');
                    if (!l || l.getAttribute('data-listener-active') === 'true') return;
                    l.setAttribute('data-listener-active', 'true');
                    l.addEventListener('click', e => {
                        try {
                            const href = l.getAttribute('href') || '#';
                            const url  = l.getAttribute('data-url') || href || '#';
                            if (href !== '#' || url !== '#') return;
                            e.preventDefault();
                            const msg = l.getAttribute('data-guard-msg') || 'Index plan route is unavailable. Please contact technical support or your domain administrator.';
                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                            let container = document.getElementById('toast-container');
                            if (!container) {
                                container = document.createElement('div');
                                container.id = 'toast-container';
                                document.body.appendChild(container);
                            }
                            if (hasBootstrap) {
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
                            l.setAttribute('data-failed-route', 'true');
                        } catch (err) {}
                    });
                } catch (error) {}
            })();
        </script>
    @endpush
    <li class="breadcrumb-item">{{__('Order Summary')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                @php
                    $planName   = data_get($plan ?? [], PlansConstants::COL_NM, __('Failed to get Plan name.'));
                    $planPrice  = data_get($plan ?? [], PlansConstants::COL_PC, __('Failed to get Plan price.'));
                    $planDurKey = data_get($plan ?? [], PlansConstants::COL_DUR, __('Failed to get Plan duration.'));
                    $duration   = Plan::$arrDuration[$planDurKey] ?? '';

                    $currencySymbol = isset($admin_payment_setting['currency_symbol']) && !empty($admin_payment_setting['currency_symbol'])
                        ? $admin_payment_setting['currency_symbol']
                        : __('Failed to get currency symbol.');
                    $quotaItems = [
                        ['key' => PlansConstants::COL_MAX_U,  'label' => __('Users')],
                        ['key' => PlansConstants::COL_MAX_CR, 'label' => __('Customers')],
                        ['key' => PlansConstants::COL_MAX_V,  'label' => __('Vendors')],
                    ];

                    $methods = [
                        ['id'=>'send_request',    'label'=>__('Manually'),       'enabled'=>'is_manually_payment_enabled', 'extra'=>[],                                           'active'=>true],
                        ['id'=>'bank_payment',    'label'=>__('Bank Transfer'),  'enabled'=>'is_bank_transfer_enabled',    'extra'=>['bank_details'],                              'active'=>false],
                        ['id'=>'stripe_payment',  'label'=>__('Stripe'),         'enabled'=>'is_stripe_enabled',           'extra'=>['stripe_key','stripe_secret'],                'active'=>false],
                        ['id'=>'paypal_payment',  'label'=>__('Paypal'),         'enabled'=>'is_paypal_enabled',           'extra'=>['paypal_client_id','paypal_secret_key'],      'active'=>false],
                        ['id'=>'paystack_payment','label'=>__('Paystack'),       'enabled'=>'is_paystack_enabled',         'extra'=>['paystack_public_key','paystack_secret_key'], 'active'=>false],
                    ];

                    $generic = [
                        'flutterwave','razorpay','mercado' => 'Mercado Pago','paytm','mollie','skrill','coingate','paymentwall',
                        'toyyibpay' => 'Toyyibpay','payfast','iyzipay' => 'Iyzipay','sspay' => 'SSPay','paytab' => 'Paytab',
                        'benefit' => 'Benefit','cashfree' => 'Cashfree','aamarpay' => 'AamarPay','paytr' => 'PayTR','yookassa' => 'Yookassa',
                        'midtrans' => 'Midtrans','xendit' => 'Xendit',
                    ];

                    foreach ($generic as $key => $label) {
                        $methodKey   = is_int($key) ? $label : $key;
                        $methodLabel = __(is_int($key) ? $label : $label);
                        $methods[] = [
                            'id'      => $methodKey . '_payment',
                            'label'   => $methodLabel,
                            'enabled' => 'is_' . $methodKey . '_enabled',
                            'extra'   => [],
                            'active'  => false,
                        ];
                    }
                @endphp
                <div class="{{ VC::CXL3 }}">
                    <div class="sticky-top" style="top:30px">
                        <div class="mt-5">
                            <div class="{{ VC::CD }} price-card price-1 wow animate__fadeInUp" data-wow-delay="0.2s" style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;">
                                <div class="card-body">
                                    <span class="price-badge {{ VC::BG_P }}">{{ $planName }}</span>
                                    <h3 class="{{ VC::MB4 }} {{ VC::FW600 }}">
                                        {{ $currencySymbol }}{{ $planPrice . ' / ' . __($duration) }}
                                    </h3>
                                    <ul class="list-unstyled my-5 {{ VC::MT3 }}">
                                        @foreach($quotaItems as $item)
                                            @php $val = data_get($plan ?? [], $item['key'], 0); @endphp
                                            <li>
                                                <span class="theme-avatar">
                                                    <i class="{{ VC::TI_CC_PLS }}"></i>
                                                </span>
                                                {{ (int) $val === -1 ? __('Unlimited') : $val }} {{ $item['label'] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_STK }}" style="top:30px">
                                <div class="{{ VC::LG_FLSH }}" id="useradd-sidenav">
                                    @foreach($methods as $method)
                                        @php
                                            $enabled   = (data_get($admin_payment_setting ?? [], $method['enabled']) === 'on');
                                            $hasExtras = true;
                                            foreach ($method['extra'] as $extraKey) {
                                                if (empty(data_get($admin_payment_setting ?? [], $extraKey))) { $hasExtras = false; break; }
                                            }
                                        @endphp
                                        @if($enabled && $hasExtras)
                                            <a href="#{{ $method['id'] }}" class="{{ VC::LGI_ACT_NBD }}{{ $method['active'] ? ' active' : '' }}">
                                                {{ $method['label'] }}
                                                <div class="{{ VC::FEND }}">
                                                    <i class="{{ VC::TI_CHV_RT }}"></i>
                                                </div>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    @if (($admin_payment_setting['is_manually_payment_enabled'] ?? 'off') == 'on')
                        <div id="send_request" class="{{ VC::CD }}">
                            <div class="card-header"><h5>{{ __('Manually') }}</h5></div>
                            <div class="tab-pane {{ (($admin_payment_setting['is_manually_payment_enabled'] ?? 'off') == 'on') ? 'active' : '' }}" id="send_request">
                                <div class="{{ VC::BD }} p-3 rounded send-request-div">
                                    <p>{{__('Requesting manual payment for the planned amount for the subscriptions plan.')}}</p>
                                </div>
                                <div class="{{ VC::CS12 }} my-2 px-2">
                                    <div class="text-end">
                                        @if($plan->id !== DatabaseConstants::DEFAULT_PLAN && $plan->id !== $user[UsersConstants::COL_PL])
                                            @if($user[UsersConstants::COL_RP] != $plan->id)
                                                @php
                                                    $planRequestSendBaseName     = VW::PLN_RQ.'.request.send';
                                                    $planRequestSendKebabName    = Str::kebab($planRequestSendBaseName);
                                                    $planRequestSendResolvedName = Route::has($planRequestSendBaseName)
                                                        ? $planRequestSendBaseName
                                                        : (Route::has($planRequestSendKebabName) ? $planRequestSendKebabName : null);
                                                    $planIdValue                 = isset($plan) && !empty($plan->id) ? $plan->id : null;
                                                    $encryptedPlanId             = $planIdValue ? Crypt::encrypt($planIdValue) : null;
                                                    $planRequestSendUrl          = ($planRequestSendResolvedName && $encryptedPlanId) ? route($planRequestSendResolvedName, $encryptedPlanId) : '#';
                                                    $planRequestSendGuardMsg     = Utility::fetchLinkMessage($lang, VW::PLN_RQ, 'send_plan_request_route_unavailable') ?? 'Send plan request route is unavailable. Please contact technical support or your domain administrator.';
                                                    $planRequestSendLinkId       = 'plan-request-send-link-'.($planIdValue ?? 'x');
                                                    $planRequestSendTitle        = __('Send Request');
                                                @endphp
                                                <a href="{{ $planRequestSendUrl }}"
                                                id="{{ $planRequestSendLinkId }}"
                                                class="{{ VC::BT_PRM }} {{ VC::MB2 }} {{ VC::ME3 }}"
                                                data-title="{{ $planRequestSendTitle }}"
                                                data-bs-toggle="tooltip"
                                                title="{{ $planRequestSendTitle }}"
                                                data-url="{{ $planRequestSendUrl }}"
                                                data-guard-msg="{{ $planRequestSendGuardMsg }}">
                                                    <span class="btn-inner--icon">{{ $planRequestSendTitle }}</span>
                                                </a>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            try {
                                                                const l = document.getElementById('{{ $planRequestSendLinkId }}');
                                                                if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                l.setAttribute('data-listener-active', 'true');
                                                                l.addEventListener('click', e => {
                                                                    try {
                                                                        const href = l.getAttribute('href') || '#';
                                                                        const url  = l.getAttribute('data-url') || href || '#';
                                                                        if (href !== '#' || url !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = l.getAttribute('data-guard-msg') || 'Send plan request route is unavailable. Please contact technical support or your domain administrator.';
                                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                        let container = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container = document.createElement('div');
                                                                            container.id = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (hasBootstrap) {
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
                                                                        l.setAttribute('data-failed-route', 'true');
                                                                    } catch (err) {}
                                                                });
                                                            } catch (error) {}
                                                        })();
                                                    </script>
                                                @endpush
                                            @else
                                                @php
                                                    $planRequestCancelBaseName     = ViewsConstants::PLN_RQ.'.request.cancel';
                                                    $planRequestCancelKebabName    = Str::kebab($planRequestCancelBaseName);
                                                    $planRequestCancelResolvedName = Route::has($planRequestCancelBaseName)
                                                        ? $planRequestCancelBaseName
                                                        : (Route::has($planRequestCancelKebabName) ? $planRequestCancelKebabName : null);
                                                    $planRequestCancelUserId       = (isset($user) && !empty($user->id)) ? $user->id : null;
                                                    $planRequestCancelUrl          = ($planRequestCancelResolvedName && $planRequestCancelUserId) ? route($planRequestCancelResolvedName, $planRequestCancelUserId) : '#';
                                                    $planRequestCancelGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PLN_RQ, 'cancel_plan_request_route_unavailable') ?? 'Cancel plan request route is unavailable. Please contact technical support or your domain administrator.';
                                                    $planRequestCancelLinkId       = 'plan-request-cancel-link-'.($planRequestCancelUserId ?? 'x');
                                                    $planRequestCancelTitle        = __('Cancel Request');
                                                @endphp
                                                <a href="{{ $planRequestCancelUrl }}"
                                                id="{{ $planRequestCancelLinkId }}"
                                                class="btn btn-danger {{ VC::MB2 }} {{ VC::ME3 }}"
                                                data-title="{{ $planRequestCancelTitle }}"
                                                data-bs-toggle="tooltip"
                                                title="{{ $planRequestCancelTitle }}"
                                                data-url="{{ $planRequestCancelUrl }}"
                                                data-guard-msg="{{ $planRequestCancelGuardMsg }}">
                                                    <span class="btn-inner--icon">{{ $planRequestCancelTitle }}</span>
                                                </a>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            try {
                                                                const l = document.getElementById('{{ $planRequestCancelLinkId }}');
                                                                if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                l.setAttribute('data-listener-active', 'true');
                                                                l.addEventListener('click', e => {
                                                                    try {
                                                                        const href = l.getAttribute('href') || '#';
                                                                        const url  = l.getAttribute('data-url') || href || '#';
                                                                        if (href !== '#' || url !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = l.getAttribute('data-guard-msg') || 'Cancel plan request route is unavailable. Please contact technical support or your domain administrator.';
                                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                        let container = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container = document.createElement('div');
                                                                            container.id = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (hasBootstrap) {
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
                                                                        l.setAttribute('data-failed-route', 'true');
                                                                    } catch (err) {}
                                                                });
                                                            } catch (error) {}
                                                        })();
                                                    </script>
                                                @endpush
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (($admin_payment_setting['is_bank_transfer_enabled'] ?? 'off') == 'on' && !empty($admin_payment_setting['bank_details']))
                        <div id="bank_payment" class="{{ VC::CD }}">
                            <div class="card-header"><h5>{{ __('Bank Transfer') }}</h5></div>
                            <div class="tab-pane {{ ((($admin_payment_setting['is_bank_transfer_enabled'] ?? 'off') == 'on') && !empty($admin_payment_setting['bank_details'])) ? 'active' : '' }}" id="bank_payment">
                                @php
                                    $bankPayBaseName     = ViewsConstants::PLN.'.pay.with.bank';
                                    $bankPayKebabName    = Str::kebab($bankPayBaseName);
                                    $bankPayResolvedName = Route::has($bankPayBaseName)
                                        ? $bankPayBaseName
                                        : (Route::has($bankPayKebabName) ? $bankPayKebabName : null);
                                    $bankPayUrl          = $bankPayResolvedName ? route($bankPayResolvedName) : '#';
                                    $bankPayGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PLN, 'pay_with_bank_route_unavailable') ?? 'Pay with bank route is unavailable. Please contact technical support or your domain administrator.';
                                    $bankPayFormId       = 'bank-payment-form';
                                @endphp
                                <form role="form"
                                    action="{{ $bankPayUrl }}"
                                    method="post"
                                    class="require-validation"
                                    id="{{ $bankPayFormId }}"
                                    enctype="multipart/form-data"
                                    data-url="{{ $bankPayUrl }}"
                                    data-guard-msg="{{ $bankPayGuardMsg }}">
                                    @csrf
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                try {
                                                    const f = document.getElementById('{{ $bankPayFormId }}');
                                                    if (!f || f.getAttribute('data-listener-active') === 'true') return;
                                                    f.setAttribute('data-listener-active', 'true');
                                                    f.addEventListener('submit', e => {
                                                        try {
                                                            const url = f.getAttribute('data-url') || '#';
                                                            const action = f.getAttribute('action') || '#';
                                                            if (url !== '#' || action !== '#') return;
                                                            e.preventDefault();
                                                            const msg = f.getAttribute('data-guard-msg') || 'Pay with bank route is unavailable. Please contact technical support or your domain administrator.';
                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            document.body.appendChild(container);
                                                            }
                                                            if (hasBootstrap) {
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
                                                            f.setAttribute('data-failed-route', 'true');
                                                        } catch (err) {}
                                                    });
                                                } catch (error) {}
                                            })();
                                        </script>
                                    @endpush
                                    <div class="{{ VC::BD }} p-3 rounded bank-payment-div">
                                        <div class="{{ VC::RW }}">
                                            <div class="col-6">
                                                <div class="custom-radio">
                                                    <label class="font-16 font-bold">{{ __('Bank Details') }} :</label>
                                                </div>
                                                <p class="{{ VC::MB0 }} pt-1 {{ VC::TXSM }}">{!! $admin_payment_setting['bank_details'] !!}</p>
                                            </div>
                                            <div class="col-6">
                                                {{ Form::label('payment_receipt', __('Payment Receipt'), ['class' => VC::FM_LB]) }}
                                                <div class="choose-file {{ VC::FM_G }}">
                                                    <input type="file" name="payment_receipt" id="image" class="{{ VC::FM_CT }}">
                                                    <p class="upload_file"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="{{ VC::RW }} mt-2">
                                            <div class="{{ VC::CM12 }}">
                                                <div class="{{ VC::DFL_AIC }}">
                                                    <div class="{{ VC::FM_G }} w-100">
                                                        <label for="bank_coupon" class="{{ VC::FM_LB }}">{{ __('Coupon') }}</label>
                                                        <input type="text" id="bank_coupon" name="coupon" class="{{ VC::FM_CT }} coupon" placeholder="{{ __('Enter Coupon Code') }}">
                                                    </div>
                                                    <div class="{{ VC::FM_G }} ms-3 {{ VC::MT4 }}">
                                                        <a href="#" class="{{ VC::TXT_MT }}" data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                                            <i class="ti ti-square-check btn-apply apply-coupon"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php $currency = $admin_payment_setting['currency_symbol'] ?? '$'; @endphp
                                        <div class="{{ VC::RW }}">
                                            <div class="col-6">
                                                <div class="custom-radio">
                                                    <label class="font-16 font-bold">{{ __('Plan Price') }} :</label>
                                                    {{ $currency }}{{ $plan[PlansConstants::COL_PC] }}
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="custom-radio">
                                                    <label class="font-16 font-bold">{{ __('Net Amount') }} : </label>
                                                    <span class="final-price">{{ $currency }}{{ $plan[PlansConstants::COL_PC] }}</span>
                                                </div>
                                                (<small>{{__('After coupon apply')}}</small>)
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CS12 }} my-2 px-2">
                                        <div class="text-end">
                                            <input type="hidden" name="plan_id" value="{{ Crypt::encrypt($plan->id) }}">
                                            <input type="submit" value="{{ __('Pay Now') }}" class="{{ VC::BT_PRM }} mb-2 {{ VC::ME3 }}">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                    @if (($admin_payment_setting['is_stripe_enabled'] ?? 'off') == 'on' && !empty($admin_payment_setting['stripe_key']) && !empty($admin_payment_setting['stripe_secret']))
                        <div id="stripe_payment" class="{{ VC::CD }}">
                            <div class="card-header"><h5>{{ __('Stripe') }}</h5></div>
                            <div class="tab-pane {{ ((($admin_payment_setting['is_stripe_enabled'] ?? 'off') == 'on') && !empty($admin_payment_setting['stripe_key']) && !empty($admin_payment_setting['stripe_secret'])) ? 'active' : '' }}" id="stripe_payment">
                                @php
                                    $stripePostBaseName     = 'stripe.post';
                                    $stripePostKebabName    = Str::kebab($stripePostBaseName);
                                    $stripePostResolvedName = Route::has($stripePostBaseName)
                                        ? $stripePostBaseName
                                        : (Route::has($stripePostKebabName) ? $stripePostKebabName : null);
                                    $stripePostUrl          = $stripePostResolvedName ? route($stripePostResolvedName) : '#';
                                    $stripePostGuardMsg     = Utility::fetchLinkMessage($lang ?? app()->getLocale(), 'stripe', 'stripe_plan_payment_route_unavailable') ?? 'Stripe payment route for plans is unavailable. Please contact technical support or your domain administrator.';
                                    $stripePostFormId       = 'payment-form';
                                @endphp
                                <form role="form"
                                    action="{{ $stripePostUrl }}"
                                    method="post"
                                    class="require-validation"
                                    id="{{ $stripePostFormId }}"
                                    data-url="{{ $stripePostUrl }}"
                                    data-guard-msg="{{ $stripePostGuardMsg }}">
                                    @csrf
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                try {
                                                    const f = document.getElementById('{{ $stripePostFormId }}');
                                                    if (!f || f.getAttribute('data-listener-active') === 'true') return;
                                                    f.setAttribute('data-listener-active', 'true');
                                                    f.addEventListener('submit', e => {
                                                        try {
                                                            const url = f.getAttribute('data-url') || '#';
                                                            const action = f.getAttribute('action') || '#';
                                                            if (url !== '#' || action !== '#') return;
                                                            e.preventDefault();
                                                            const msg = f.getAttribute('data-guard-msg') || 'Stripe payment route is unavailable. Please contact technical support or your domain administrator.';
                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (hasBootstrap) {
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
                                                            f.setAttribute('data-failed-route', 'true');
                                                        } catch (err) {}
                                                    });
                                                } catch (error) {}
                                            })();
                                        </script>
                                    @endpush
                                    <div class="{{ VC::BD }} p-3 rounded stripe-payment-div">
                                        <div class="{{ VC::RW }}">
                                            <div class="col-sm-8">
                                                <div class="custom-radio">
                                                    <label class="font-16 font-weight-bold">{{ __('Credit / Debit Card') }}</label>
                                                </div>
                                                <p class="{{ VC::MB0 }} pt-1 {{ VC::TXSM }}">
                                                    {{ __('Safe money transfer using your bank account. We support Mastercard, Visa, Discover and American express.') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::CM12 }}">
                                                <div class="{{ VC::FM_G }}">
                                                    <label for="card-name-on" class="{{ VC::FM_LB }} text-dark">{{ __('Name on card') }}</label>
                                                    <input type="text" name="name" id="card-name-on" class="{{ VC::FM_CT }} required" placeholder="{{ $user[UsersConstants::COL_NM] }}">
                                                </div>
                                            </div>
                                            <div class="{{ VC::CM12 }}">
                                                <div id="card-element"></div>
                                                <div id="card-errors" role="alert"></div>
                                            </div>
                                            <div class="{{ VC::CM12 }} {{ VC::MT4 }}">
                                                <div class="{{ VC::DFL_AIC }}">
                                                    <div class="{{ VC::FM_G }} w-100">
                                                        <label for="stripe_coupon" class="{{ VC::FM_LB }}">{{ __('Coupon') }}</label>
                                                        <input type="text" id="stripe_coupon" name="coupon" class="{{ VC::FM_CT }} coupon" placeholder="{{ __('Enter Coupon Code') }}">
                                                    </div>
                                                    <div class="{{ VC::FM_G }} ms-3 {{ VC::MT4 }}">
                                                        <a href="#" class="{{ VC::TXT_MT }}" data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                                            <i class="ti ti-square-check btn-apply apply-coupon"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="{{ VC::C12 }}">
                                                <div class="error" style="display: none;">
                                                    <div class='alert-danger alert'>{{ __('Please correct the errors and try again.') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CS12 }} my-2 px-2">
                                        <div class="text-end">
                                            <input type="hidden" name="plan_id" value="{{ Crypt::encrypt($plan->id) }}">
                                            <input type="submit" value="{{ __('Pay Now') }}" class="{{ VC::BT_PRM }} mb-2 {{ VC::ME3 }}">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                    @if (isset($admin_payment_setting['is_skrill_enabled']) && $admin_payment_setting['is_skrill_enabled'] == 'on')
                        <div id="skrill_payment" class="{{ VC::CD }}">
                            <div class="card-header"><h5>{{ __('Skrill') }}</h5></div>
                            <div class="tab-pane" id="skrill_payment">
                                @php
                                    $skrillPayBaseName     = ViewsConstants::PLN.'.pay.with.skrill';
                                    $skrillPayKebabName    = Str::kebab($skrillPayBaseName);
                                    $skrillPayResolvedName = Route::has($skrillPayBaseName)
                                        ? $skrillPayBaseName
                                        : (Route::has($skrillPayKebabName) ? $skrillPayKebabName : null);
                                    $skrillPayUrl          = $skrillPayResolvedName ? route($skrillPayResolvedName) : '#';
                                    $skrillPayGuardMsg     = Utility::fetchLinkMessage($lang, 'skrill', 'pay_with_skrill_route_unavailable') ?? 'Plan payment with skrill route is unavailable. Please contact technical support or your domain administrator.';
                                    $skrillPayFormId       = 'skrill-payment-form';
                                @endphp
                                <form role="form"
                                    action="{{ $skrillPayUrl }}"
                                    method="post"
                                    class="require-validation"
                                    id="{{ $skrillPayFormId }}"
                                    accept-charset="UTF-8"
                                    data-url="{{ $skrillPayUrl }}"
                                    data-guard-msg="{{ $skrillPayGuardMsg }}">
                                    @csrf
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                try {
                                                    const f = document.getElementById('{{ $skrillPayFormId }}');
                                                    if (!f || f.getAttribute('data-listener-active') === 'true') return;
                                                    f.setAttribute('data-listener-active', 'true');
                                                    f.addEventListener('submit', e => {
                                                        try {
                                                            const url = f.getAttribute('data-url') || '#';
                                                            const action = f.getAttribute('action') || '#';
                                                            if (url !== '#' || action !== '#') return;
                                                            e.preventDefault();
                                                            const msg = f.getAttribute('data-guard-msg') || 'Pay with skrill route is unavailable. Please contact technical support or your domain administrator.';
                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (hasBootstrap) {
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
                                                            f.setAttribute('data-failed-route', 'true');
                                                        } catch (err) {}
                                                    });
                                                } catch (error) {}
                                            })();
                                        </script>
                                    @endpush
                                    <input type="hidden" name="id" value="{{ date('Y-m-d') }}-{{ strtotime(date('Y-m-d H:i:s')) }}-payatm">
                                    <input type="hidden" name="order_id" value="{{ str_pad(!empty($order->id) ? $order->id + 1 : 0 + 1, 4, '100', STR_PAD_LEFT) }}">
                                    @php
                                        $skrill_data = [
                                            'transaction_id' => md5(date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id'),
                                            'user_id' => 'user_id',
                                            'amount' => 'amount',
                                            'currency' => 'currency',
                                        ];
                                        session()->put('skrill_data', $skrill_data);
                                    @endphp
                                    <input type="hidden" name="plan_id" value="{{ Crypt::encrypt($plan->id) }}">
                                    <input type="hidden" name="total_price" id="skrill_total_price" value="{{ $plan[PlansConstants::COL_PC] }}" class="{{ VC::FM_CT }}">
                                    <div class="{{ VC::BD }} p-3 {{ VC::MB3 }} rounded">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::CM12 }} {{ VC::MT4 }}">
                                                <div class="{{ VC::DFL_AIC }}">
                                                    <div class="{{ VC::FM_G }} w-100">
                                                        <label for="skrill_coupon" class="{{ VC::FM_LB }}">{{ __('Coupon') }}</label>
                                                        <input type="text" id="skrill_coupon" name="coupon" class="{{ VC::FM_CT }} coupon" placeholder="{{ __('Enter Coupon Code') }}">
                                                    </div>
                                                    <div class="{{ VC::FM_G }} ms-3 {{ VC::MT4 }}">
                                                        <a class="{{ VC::TXT_MT }}" data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                                            <i class="ti ti-square-check btn-apply apply-coupon"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CS12 }} my-2 px-2">
                                        <div class="text-end">
                                            <button class="{{ VC::BT_PRM }} mb-2 {{ VC::ME3 }}" id="pay_with_skrill" type="submit">
                                                {{ __('Pay Now') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                    @if (isset($admin_payment_setting['is_payfast_enabled']) && $admin_payment_setting['is_payfast_enabled'] == 'on')
                        @if (($admin_payment_setting['is_payfast_enabled'] ?? 'off') == 'on'
                            && !empty($admin_payment_setting['payfast_merchant_id'])
                            && !empty($admin_payment_setting['payfast_merchant_key'])
                            && !empty($admin_payment_setting['payfast_signature'])
                            && !empty($admin_payment_setting['payfast_mode']))
                            <div id="payfast_payment" class="{{ VC::CD }}">
                                <div class="card-header"><h5>{{ __('Payfast') }}</h5></div>
                                @php $pfHost = $admin_payment_setting['payfast_mode'] == 'sandbox' ? 'sandbox.payfast.co.za' : 'www.payfast.co.za'; @endphp
                                <div class="tab-pane {{ 'active' }}">
                                    @php
                                        $pfHostValue        = isset($pfHost) && !empty($pfHost) ? $pfHost : null;
                                        $payfastActionUrl   = $pfHostValue ? ('https://'.$pfHostValue.'/eng/process') : '#';
                                        $payfastGuardMsg    = Utility::fetchLinkMessage($lang, 'payfast', 'payfast_process_route_unavailable') ?? 'PayFast plan payment route is unavailable. Please contact technical support or your domain administrator.';
                                        $payfastFormId      = 'payfast-form';
                                    @endphp
                                    <form role="form"
                                        action="{{ $payfastActionUrl }}"
                                        method="post"
                                        class="require-validation"
                                        id="{{ $payfastFormId }}"
                                        data-url="{{ $payfastActionUrl }}"
                                        data-guard-msg="{{ $payfastGuardMsg }}">
                                        @csrf
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    try {
                                                        const f = document.getElementById('{{ $payfastFormId }}');
                                                        if (!f || f.getAttribute('data-listener-active') === 'true') return;
                                                        f.setAttribute('data-listener-active', 'true');
                                                        f.addEventListener('submit', e => {
                                                            try {
                                                                const url = f.getAttribute('data-url') || '#';
                                                                const action = f.getAttribute('action') || '#';
                                                                if (url !== '#' || action !== '#') return;
                                                                e.preventDefault();
                                                                const msg = f.getAttribute('data-guard-msg') || 'PayFast process route is unavailable. Please contact technical support or your domain administrator.';
                                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                let container = document.getElementById('toast-container');
                                                                if (!container) {
                                                                    container = document.createElement('div');
                                                                    container.id = 'toast-container';
                                                                    document.body.appendChild(container);
                                                                }
                                                                if (hasBootstrap) {
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
                                                                f.setAttribute('data-failed-route', 'true');
                                                            } catch (err) {}
                                                        });
                                                    } catch (error) {}
                                                })();
                                            </script>
                                        @endpush
                                        <div class="{{ VC::BD }} p-3 {{ VC::MB3 }} rounded">
                                            <div class="{{ VC::RW }}">
                                                <div class="{{ VC::CM12 }} {{ VC::MT4 }}">
                                                    <div class="{{ VC::DFL_AIC }}">
                                                        <div class="{{ VC::FM_G }} w-100">
                                                            <label for="payfast_coupon" class="{{ VC::FM_LB }}">{{ __('Coupon') }}</label>
                                                            <input type="text" id="payfast_coupon" name="coupon" class="{{ VC::FM_CT }} coupon" placeholder="{{ __('Enter Coupon Code') }}">
                                                        </div>
                                                        <div class="{{ VC::FM_G }} ms-3 {{ VC::MT4 }}">
                                                            <a class="{{ VC::TXT_MT }} apply-coupon" data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                                                <i class="ti ti-square-check btn-apply apply-coupon"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="get-payfast-inputs"></div>
                                        <div class="{{ VC::CS12 }} my-2 px-2">
                                            <div class="text-end">
                                                <input type="hidden" name="plan_id" id="plan_id" value="{{ Crypt::encrypt($plan->id) }}">
                                                <input type="submit" value="{{ __('Pay Now') }}" id="payfast-get-status" class="{{ VC::BT_PRM }} mb-2 {{ VC::ME3 }}">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endif
                    @php
                        $defaultFormClass = 'w3-container w3-display-middle w3-card-4';
                        $couponGateways = [
                            [
                                'cond'       => (($admin_payment_setting['is_paypal_enabled'] ?? 'off') == 'on')
                                                && !empty($admin_payment_setting['paypal_client_id'])
                                                && !empty($admin_payment_setting['paypal_secret_key']),
                                'key'        => 'paypal',
                                'title'      => 'Paypal',
                                'action'     => route(VW::PLN.'.pay.with.paypal'),
                                'formId'     => 'paypal-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => null,
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_paystack_enabled']) && $admin_payment_setting['is_paystack_enabled'] == 'on'),
                                'key'        => 'paystack',
                                'title'      => 'Paystack',
                                'action'     => route(VW::PLN.'.pay.with.paystack'),
                                'formId'     => 'paystack-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'button',
                                'submitId'   => 'pay_with_paystack',
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_flutterwave_enabled']) && $admin_payment_setting['is_flutterwave_enabled'] == 'on'),
                                'key'        => 'flutterwave',
                                'title'      => 'Flutterwave',
                                'action'     => route(VW::PLN.'.pay.with.flutterwave'),
                                'formId'     => 'flutterwave-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'button',
                                'submitId'   => 'pay_with_flutterwave',
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_razorpay_enabled']) && $admin_payment_setting['is_razorpay_enabled'] == 'on'),
                                'key'        => 'razorpay',
                                'title'      => 'Razorpay',
                                'action'     => route(VW::PLN.'.pay.with.razorpay'),
                                'formId'     => 'razorpay-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'button',
                                'submitId'   => 'pay_with_razorpay',
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_mercado_enabled']) && $admin_payment_setting['is_mercado_enabled'] == 'on'),
                                'key'        => 'mercado',
                                'title'      => 'Mercado Pago',
                                'action'     => route(VW::PLN.'.pay.with.mercado'),
                                'formId'     => 'mercado-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => 'pay_with_mercado',
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_paytm_enabled']) && $admin_payment_setting['is_paytm_enabled'] == 'on'),
                                'key'        => 'paytm',
                                'title'      => 'Paytm',
                                'action'     => route(VW::PLN.'.pay.with.paytm'),
                                'formId'     => 'paytm-payment-form',
                                'formClass'  => 'require-validation',
                                'submitType' => 'submit',
                                'submitId'   => 'pay_with_paytm',
                                'extraHidden'=> ['total_price' => $plan[\PlansConstants::COL_PC]],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_mollie_enabled']) && $admin_payment_setting['is_mollie_enabled'] == 'on'),
                                'key'        => 'mollie',
                                'title'      => 'Mollie',
                                'action'     => route(VW::PLN.'.pay.with.mollie'),
                                'formId'     => 'mollie-payment-form',
                                'formClass'  => 'require-validation',
                                'submitType' => 'submit',
                                'submitId'   => 'pay_with_mollie',
                                'extraHidden'=> ['total_price' => $plan[\PlansConstants::COL_PC]],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_coingate_enabled']) && $admin_payment_setting['is_coingate_enabled'] == 'on'),
                                'key'        => 'coingate',
                                'title'      => 'Coingate',
                                'action'     => route(VW::PLN.'.pay.with.coingate'),
                                'formId'     => 'coingate-payment-form',
                                'formClass'  => 'require-validation',
                                'submitType' => 'submit',
                                'submitId'   => 'pay_with_coingate',
                                'extraHidden'=> ['total_price' => $plan[\PlansConstants::COL_PC], 'counpon' => ''],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_paymentwall_enabled']) && $admin_payment_setting['is_paymentwall_enabled'] == 'on'),
                                'key'        => 'paymentwall',
                                'title'      => 'Paymentwall',
                                'action'     => route(VW::PLN.'.paymentwallpayment'),
                                'formId'     => 'paymentwall-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => 'pay_with_paymentwall',
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_toyyibpay_enabled']) && $admin_payment_setting['is_toyyibpay_enabled'] == 'on'),
                                'key'        => 'toyyibpay',
                                'title'      => 'Toyyibpay',
                                'action'     => route(VW::PLN.'.toyyibpaypayment'),
                                'formId'     => 'toyyibpay-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => null,
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_iyzipay_enabled']) && $admin_payment_setting['is_iyzipay_enabled'] == 'on'),
                                'key'        => 'iyzipay',
                                'title'      => 'Iyzipay',
                                'action'     => route('iyzipay.payment.init'),
                                'formId'     => 'iyzipay-payment-form',
                                'formClass'  => 'require-validation',
                                'submitType' => 'submit',
                                'submitId'   => 'payfast-get-status',
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_sspay_enabled']) && $admin_payment_setting['is_sspay_enabled'] == 'on'),
                                'key'        => 'sspay',
                                'title'      => 'SSPay',
                                'action'     => route(VW::PLN.'.sspaypayment'),
                                'formId'     => 'sspay-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => null,
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_paytab_enabled']) && $admin_payment_setting['is_paytab_enabled'] == 'on'),
                                'key'        => 'paytab',
                                'title'      => 'PayTab',
                                'action'     => route(VW::PLN.'.pay.with.paytab'),
                                'formId'     => 'paytab-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => null,
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_benefit_enabled']) && $admin_payment_setting['is_benefit_enabled'] == 'on'),
                                'key'        => 'benefit',
                                'title'      => 'Benefit',
                                'action'     => route(VW::PLN.'.pay.with.benefit'),
                                'formId'     => 'benefit-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => null,
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_cashfree_enabled']) && $admin_payment_setting['is_cashfree_enabled'] == 'on'),
                                'key'        => 'cashfree',
                                'title'      => 'Cashfree',
                                'action'     => route(VW::PLN.'.pay.with.cashfree'),
                                'formId'     => 'cashfree-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => null,
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_aamarpay_enabled']) && $admin_payment_setting['is_aamarpay_enabled'] == 'on'),
                                'key'        => 'aamarpay',
                                'title'      => 'AamarPay',
                                'action'     => route(VW::PLN.'.pay.with.aamarpay'),
                                'formId'     => 'aamarpay-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => null,
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_paytr_enabled']) && $admin_payment_setting['is_paytr_enabled'] == 'on'),
                                'key'        => 'paytr',
                                'title'      => 'PayTR',
                                'action'     => route(VW::PLN.'.pay.with.paytr', $plan->id),
                                'formId'     => 'paytr-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => null,
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_yookassa_enabled']) && $admin_payment_setting['is_yookassa_enabled'] == 'on'),
                                'key'        => 'yookassa',
                                'title'      => 'Yookassa',
                                'action'     => route(VW::PLN.'.pay.with.yookassa', $plan->id),
                                'formId'     => 'yookassa-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => null,
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_midtrans_enabled']) && $admin_payment_setting['is_midtrans_enabled'] == 'on'),
                                'key'        => 'midtrans',
                                'title'      => 'Midtrans',
                                'action'     => route(VW::PLN.'.pay.with.midtrans', $plan->id),
                                'formId'     => 'midtrans-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => null,
                                'extraHidden'=> [],
                            ],
                            [
                                'cond'       => (isset($admin_payment_setting['is_xendit_enabled']) && $admin_payment_setting['is_xendit_enabled'] == 'on'),
                                'key'        => 'xendit',
                                'title'      => 'Xendit',
                                'action'     => route(VW::PLN.'.pay.with.xendit', $plan->id),
                                'formId'     => 'xendit-payment-form',
                                'formClass'  => $defaultFormClass,
                                'submitType' => 'submit',
                                'submitId'   => null,
                                'extraHidden'=> [],
                            ],
                        ];
                        $activeKey = null;
                        if (($admin_payment_setting['is_stripe_enabled'] ?? 'off') !== 'on')
                            foreach ($couponGateways as $g)
                                if ($g['key'] === 'paypal' && $g['cond']) { $activeKey = 'paypal'; break; }
                        if (!$activeKey)
                            foreach ($couponGateways as $g)
                                if ($g['cond']) { $activeKey = $g['key']; break; }
                    @endphp
                    @foreach ($couponGateways as $gw)
                        @continue(!$gw['cond'])
                        <div id="{{ $gw['key'] }}_payment" class="{{ VC::CD }}">
                            <div class="card-header"><h5>{{ __($gw['title']) }}</h5></div>
                            <div class="tab-pane {{ $activeKey === $gw['key'] ? 'active' : '' }}" id="{{ $gw['key'] }}_payment">
                                @php
                                    $gatewayKey                    = e(data_get($gw ?? [], 'key', 'gateway'));
                                    $gatewayFormId                 = e(data_get($gw ?? [], 'formId', 'gateway-payment-form'));
                                    $gatewayFormClass              = e(data_get($gw ?? [], 'formClass', 'require-validation'));
                                    $gatewayProvidedAction         = data_get($gw ?? [], 'action', '#');
                                    $routeNameByKey = [
                                        'paypal'      => ViewsConstants::PLN.'.pay.with.paypal',
                                        'paystack'    => ViewsConstants::PLN.'.pay.with.paystack',
                                        'flutterwave' => ViewsConstants::PLN.'.pay.with.flutterwave',
                                        'razorpay'    => ViewsConstants::PLN.'.pay.with.razorpay',
                                        'mercado'     => ViewsConstants::PLN.'.pay.with.mercado',
                                        'paytm'       => ViewsConstants::PLN.'.pay.with.paytm',
                                        'mollie'      => ViewsConstants::PLN.'.pay.with.mollie',
                                        'coingate'    => ViewsConstants::PLN.'.pay.with.coingate',
                                        'paymentwall' => ViewsConstants::PLN.'.paymentwallpayment',
                                        'toyyibpay'   => ViewsConstants::PLN.'.toyyibpaypayment',
                                        'iyzipay'     => 'iyzipay.payment.init',
                                        'sspay'       => ViewsConstants::PLN.'.sspaypayment',
                                        'paytab'      => ViewsConstants::PLN.'.pay.with.paytab',
                                        'benefit'     => ViewsConstants::PLN.'.pay.with.benefit',
                                        'cashfree'    => ViewsConstants::PLN.'.pay.with.cashfree',
                                        'aamarpay'    => ViewsConstants::PLN.'.pay.with.aamarpay',
                                        'paytr'       => ViewsConstants::PLN.'.pay.with.paytr',
                                        'yookassa'    => ViewsConstants::PLN.'.pay.with.yookassa',
                                        'midtrans'    => ViewsConstants::PLN.'.pay.with.midtrans',
                                        'xendit'      => ViewsConstants::PLN.'.pay.with.xendit',
                                    ];

                                    $gatewayBaseName               = $routeNameByKey[$gatewayKey] ?? null;
                                    $gatewayKebabName              = $gatewayBaseName ? Str::kebab($gatewayBaseName) : null;
                                    $gatewayResolvedName           = ($gatewayBaseName && Route::has($gatewayBaseName))
                                        ? $gatewayBaseName
                                        : (($gatewayKebabName && Route::has($gatewayKebabName)) ? $gatewayKebabName : null);

                                    $gatewayActionUrl              = $gatewayResolvedName ? ($gatewayProvidedAction ?? '#') : '#';

                                    $gatewayGuardMsg               = Utility::fetchLinkMessage($lang, $gatewayKey, $gatewayKey.'_plan_payment_route_unavailable') ?? ('Pay with '.ucfirst($gatewayKey).' route is unavailable. Please contact technical support or your domain administrator.');
                                @endphp
                                <form role="form"
                                    action="{{ $gatewayActionUrl }}"
                                    method="post"
                                    class="{{ $gatewayFormClass }}"
                                    id="{{ $gatewayFormId }}"
                                    data-url="{{ $gatewayActionUrl }}"
                                    data-guard-msg="{{ $gatewayGuardMsg }}">
                                    @csrf
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                try {
                                                    const f = document.getElementById('{{ $gatewayFormId }}');
                                                    if (!f || f.getAttribute('data-listener-active') === 'true') return;
                                                    f.setAttribute('data-listener-active', 'true');
                                                    f.addEventListener('submit', e => {
                                                        try {
                                                            const url = f.getAttribute('data-url') || '#';
                                                            const action = f.getAttribute('action') || '#';
                                                            if (url !== '#' || action !== '#') return;
                                                            e.preventDefault();
                                                            const msg = f.getAttribute('data-guard-msg') || 'Payment route is unavailable. Please contact technical support or your domain administrator.';
                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (hasBootstrap) {
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
                                                            f.setAttribute('data-failed-route', 'true');
                                                        } catch (err) {}
                                                    });
                                                } catch (error) {}
                                            })();
                                        </script>
                                    @endpush
                                    <input type="hidden" name="plan_id" value="{{ Crypt::encrypt($plan->id) }}">
                                    @foreach(($gw['extraHidden'] ?? []) as $name => $value)
                                        <input type="hidden" name="{{ $name }}" id="{{ $gw['key'] }}_{{ $name }}" value="{{ $value }}" class="{{ VC::FM_CT }}">
                                    @endforeach
                                    @if($gw['key'] === 'paytm')
                                        <div class="{{ VC::BD }} p-3 {{ VC::MB3 }} rounded">
                                            <div class="{{ VC::RW }}">
                                                <div class="{{ VC::CM12 }}">
                                                    <div class="{{ VC::FM_G }}">
                                                        <label for="mobile_number" class="{{ VC::FM_LB }}">{{ __('Mobile Number') }}</label>
                                                        <input type="text" id="mobile_number" name="mobile_number" class="{{ VC::FM_CT }} coupon" placeholder="{{ __('Enter Mobile Number') }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="{{ VC::BD }} p-3 {{ VC::MB3 }} rounded">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::CM12 }} {{ VC::MT4 }}">
                                                <div class="{{ VC::DFL_AIC }}">
                                                    <div class="{{ VC::FM_G }} w-100">
                                                        <label for="{{ $gw['key'] }}_coupon" class="{{ VC::FM_LB }}">{{ __('Coupon') }}</label>
                                                        <input type="text" id="{{ $gw['key'] }}_coupon" name="coupon" class="{{ VC::FM_CT }} coupon" placeholder="{{ __('Enter Coupon Code') }}">
                                                    </div>
                                                    <div class="{{ VC::FM_G }} ms-3 {{ VC::MT4 }}">
                                                        <a class="{{ VC::TXT_MT }}" data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                                            <i class="ti ti-square-check btn-apply apply-coupon"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                @if($gw['key'] === 'paystack')
                                                    <div class="col-12 text-right paymentwall-coupon-tr" style="display: none">
                                                        <b>{{__('Coupon Discount')}}</b> : <b class="paymentwall-coupon-price"></b>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CS12 }} my-2 px-2">
                                        <div class="text-end">
                                            @if(($gw['submitType'] ?? 'submit') === 'button')
                                                <input type="button" id="{{ $gw['submitId'] }}" value="{{ __('Pay Now') }}" class="{{ VC::BT_PRM }} mb-2 {{ VC::ME3 }}">
                                            @else
                                                <button type="submit" @if(!empty($gw['submitId'])) id="{{ $gw['submitId'] }}" @endif class="{{ VC::BT_PRM }} mb-2 {{ VC::ME3 }}">
                                                    {{ __('Pay Now') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
