@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Config\Constants\{DatabaseConstants, SettingsConstants};
    use App\Models\{Purchase, User, Utility};
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $settings = Utility::settings();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Purchase Detail')}}
@endsection
@if(isset($purchase) && !empty($purchase) && !empty($purchase->id))
    @push(StacksConstants::ADM_SCR_PG)
        <script async>
            (function () {
                if (!window.translations) { window.translations = {}; }
                const t = {
                "ar": { "shipping_unavailable": "خيار الشحن غير متاح حاليًا." },
                "da": { "shipping_unavailable": "Forsendelsesmuligheden er ikke tilgængelig lige nu." },
                "de": { "shipping_unavailable": "Versandoption ist derzeit nicht verfügbar." },
                "en": { "shipping_unavailable": "Shipping option is unavailable right now." },
                "es": { "shipping_unavailable": "La opción de envío no está disponible en este momento." },
                "fr": { "shipping_unavailable": "L’option d’expédition est indisponible pour le moment." },
                "he": { "shipping_unavailable": "אפשרות המשלוח אינה זמינה כעת." },
                "it": { "shipping_unavailable": "L’opzione di spedizione non è al momento disponibile." },
                "ja": { "shipping_unavailable": "配送オプションは現在利用できません。" },
                "nl": { "shipping_unavailable": "Verzendoptie is momenteel niet beschikbaar." },
                "pl": { "shipping_unavailable": "Opcja wysyłki jest obecnie niedostępna." },
                "pt": { "shipping_unavailable": "Opção de envio indisponível no momento." },
                "pt-br": { "shipping_unavailable": "Opção de frete indisponível no momento." },
                "ru": { "shipping_unavailable": "Опция доставки сейчас недоступна." },
                "tr": { "shipping_unavailable": "Gönderim seçeneği şu anda kullanılamıyor." },
                "zh": { "shipping_unavailable": "配送选项目前不可用。" }
                };
                Object.keys(t).forEach(function (k) { window.translations[k] = { ...(window.translations[k] || {}), ...t[k] }; });
            })();
        </script>
        <script defer>
            (function () {
                const $ = window.jQuery;
                if (!$) { try { console.error("jQuery unavailable"); } catch (_) { } return; }
                const qs = (s, r = document) => r.querySelector(s);
                const errFb = "# ERROR";
                const dataClientLocalized = "data-client-localized";
                const dataGuardMsg = "data-guard-msg";
                const dataListenerGuard = "data-shipping-listener";
                const dataSvLocalized = "data-sv-localized";
                const msgKey = "shipping_unavailable";
                const getMsg = (el) => {
                let msg = errFb;
                if (el.getAttribute(dataSvLocalized) === "true" || el.getAttribute(dataClientLocalized) === "true") { msg = el.getAttribute(dataGuardMsg) || errFb; }
                else {
                    let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
                    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                    msg = window.translations?.[lang]?.[msgKey] || el.getAttribute(dataGuardMsg) || window.translations?.["en"]?.[msgKey] || errFb;
                    if (msg !== errFb) { el.setAttribute(dataGuardMsg, msg); el.setAttribute(dataClientLocalized, "true"); }
                }
                return msg;
                };
                const ensureToastContainer = () => {
                const id = "np-toast-container";
                let c = qs(`#${id}`);
                if (c) { return c; }
                c = document.createElement("div");
                c.id = id;
                c.setAttribute("aria-live", "polite");
                c.setAttribute("aria-atomic", "true");
                c.style.position = "fixed";
                c.style.top = "1rem";
                c.style.right = "1rem";
                document.body.appendChild(c);
                return c;
                };
                const showError = (el, message) => {
                const hasBootstrap = (qs('link[rel="stylesheet"][href*="bootstrap"]') || qs('link[href*="bootstrap"]'));
                if (hasBootstrap) {
                    const container = ensureToastContainer();
                    const toastId = "np-toast";
                    let t = qs(`#${toastId}`, container);
                    if (!t) {
                    t = document.createElement("div");
                    t.id = toastId;
                    t.className = "toast";
                    t.setAttribute("role", "alert");
                    t.setAttribute("aria-live", "assertive");
                    t.setAttribute("aria-atomic", "true");
                    t.innerHTML = '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                    container.appendChild(t);
                    }
                    const body = qs(".toast-body", t);
                    if (body) { body.textContent = message ?? errFb; }
                    try { if (window.bootstrap?.Toast) { new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show(); } else { alert(message ?? errFb); } }
                    catch (_) { alert(message ?? errFb); }
                } else { alert(message ?? errFb); }
                };
                const onPointerUp = (ev) => {
                const el = ev.currentTarget;
                const url = el.getAttribute("data-url");
                const href = el.tagName === "A" ? el.getAttribute("href") : el.tagName === "FORM" ? el.getAttribute("action") : "";
                if ((!url || url === "#") && (!href || href === "#")) { const message = getMsg(el); showError(el, message); return; }
                const is_display = $("#shipping").is(":checked");
                try {
                    $.ajax({
                    url: url ?? href ?? "",
                    type: "get",
                    data: { is_display },
                    success: function () { },
                    error: function () { const message = getMsg(el); showError(el, message); }
                    });
                } catch (_) { const message = getMsg(el); showError(el, message); }
                };
                const attach = (el) => {
                if (!el) { return; }
                if (el.getAttribute(dataListenerGuard) === "true") { return; }
                el.setAttribute(dataListenerGuard, "true");
                $(el).on("pointerup", onPointerUp);
                const mo = new MutationObserver((m, o) => { if (!document.body.contains(el)) { $(el).off("pointerup", onPointerUp); o.disconnect(); } });
                mo.observe(document.body, { childList: true, subtree: true });
                };
                const bind = () => {
                const el = document.getElementById("shipping");
                if (el) { attach(el); return; }
                const w = new MutationObserver((m, o) => { const n = document.getElementById("shipping"); if (n) { attach(n); o.disconnect(); } });
                w.observe(document.body, { childList: true, subtree: true });
                };
                if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", bind, { once: true }); }
                else { bind(); }
            })();
        </script>
    @endpush
    @section(YieldingConstants::ADM_BDC)
        <li class="breadcrumb-item">
            <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
            {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
                {{ __('Dashboard') }}
            </a>
        </li>
        @php
            $purchaseIndexBase              = ViewsConstants::PRC.'.index';
            $purchaseIndexKebab             = Str::kebab($purchaseIndexBase);
            $purchaseIndexResolved          = Route::has($purchaseIndexBase) ? $purchaseIndexBase : (Route::has($purchaseIndexKebab) ? $purchaseIndexKebab : null);
            $purchaseIndexUrl               = $purchaseIndexResolved ? route($purchaseIndexResolved) : '#';
            $purchaseIndexGuardMsg          = Utility::fetchLinkMessage($lang, ViewsConstants::PRC, 'purchase_index_route_unavailable') ?? 'Purchase index route is unavailable. Please contact technical support or your domain administrator.';
            $purchaseIndexLinkId            = 'purchase-index-breadcrumb-link';
        @endphp
        <li class="breadcrumb-item">
            <a href="{{ $purchaseIndexUrl }}" id="{{ $purchaseIndexLinkId }}" class="{{ VC::BT_LNK ?? '' }}" data-ajax-popup="true" data-title="{{ __('Purchase') }}" data-url="{{ $purchaseIndexUrl }}" data-guard-msg="{{ $purchaseIndexGuardMsg }}" data-sv-localized="true">{{ __('Purchase') }}</a>
        </li>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/purchases/index.js') }}" defer></script>
        @endpush
        <li class="breadcrumb-item">{{ ($user instanceof User && method_exists($user, 'purchaseNumberFormat')) ? $user->purchaseNumberFormat($purchase->id) : __('Failed to format purchase number') }}</li>
    @endsection
    @section('content')
        @php
            $purchaseId = data_get($purchase,'id');
            $purchaseStatus = (int)(data_get($purchase,'status') ?? -1);
            $fmtDate = function($val,$fb) use($user){ return ($val && $user && method_exists($user,'dateFormat')) ? ($user->dateFormat($val) ?? $fb) : $fb; };
            $fmtMoney = function($val){ return request()->user()?->priceFormat($val) ?? number_format((float)$val,2); };
            $numberFmt = function($v){ return number_format((float)$v,2); };
        @endphp
        @can('send purchase')
            @if($purchaseStatus !== 4)
                <div class="{{ VC::RW }}">
                    <div class="{{ VC::C12 }}">
                        <div class="{{ VC::CD }}">
                            <div class="card-body">
                                <div class="{{ VC::RW }} timeline-wrapper">
                                    <div class="{{ VC::CM6 }} {{ VC::CL4 }} col-xl-4">
                                        <div class="timeline-icons"><span class="timeline-dots"></span><i class="{{ VC::TI_PLS }} text-primary"></i></div>
                                        <h6 class="text-primary {{ VC::MY3 }}">{{ __('Create Purchase') }}</h6>
                                        <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB3 }}"><i class="ti ti-clock {{ VC::MR2 }}"></i>{{ __('Created on ') }}{{ $fmtDate(data_get($purchase,'purchase_date'), __('No purchase date available')) }}</p>
                                        @can('edit purchase')
                                            @php
                                                $purchaseIdVal          = isset($purchaseId) ? $purchaseId : null;
                                                $purchaseEncId          = $purchaseIdVal ? Crypt::encrypt($purchaseIdVal) : null;
                                                $purchaseEditBase       = VW::PRC.'.edit';
                                                $purchaseEditKebab      = Str::kebab($purchaseEditBase);
                                                $purchaseEditResolved   = Route::has($purchaseEditBase) ? $purchaseEditBase : (Route::has($purchaseEditKebab) ? $purchaseEditKebab : null);
                                                $purchaseEditUrl        = ($purchaseEditResolved && $purchaseEncId) ? route($purchaseEditResolved, [$purchaseEncId]) : '#';
                                                $purchaseEditGuardMsg   = Utility::fetchLinkMessage($lang, VW::PRC, 'edit_purchase_route_unavailable') ?? 'Edit purchase route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <a href="{{ $purchaseEditUrl }}"
                                            class="{{ VC::BT_SM_PM }} edit-purchase"
                                            data-bs-toggle="tooltip"
                                            data-original-title="{{ __('Edit') }}"
                                            data-url="{{ $purchaseEditUrl }}"
                                            data-guard-msg="{{ $purchaseEditGuardMsg }}"
                                            data-sv-localized="true">
                                                <i class="{{ VC::TI_PC }} {{ VC::MR2 }}"></i>{{ __('Edit') }}
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script src="{{ asset('assets/js/routes/purchases/edit.js') }}" defer></script>
                                            @endpush
                                        @endcan
                                    </div>
                                    <div class="{{ VC::CM6 }} {{ VC::CL4 }} col-xl-4">
                                        <div class="timeline-icons"><span class="timeline-dots"></span><i class="ti ti-mail text-warning"></i></div>
                                        <h6 class="text-warning {{ VC::MY3 }}">{{ __('Send Purchase') }}</h6>
                                        <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB3 }}">
                                            @if($purchaseStatus !== 0)
                                                <i class="ti ti-clock {{ VC::MR2 }}"></i>{{ __('Sent on') }} {{ $fmtDate(data_get($purchase,'send_date'), __('No send date available')) }}
                                            @else
                                                @can('send purchase')
                                                    <small>{{ __('Status') }} : {{ __('Not Sent') }}</small>
                                                @endcan
                                            @endif
                                        </p>
                                        @if($purchaseStatus === 0)
                                            @can('send purchase')
                                                @php
                                                    $purchaseIdVal            = isset($purchaseId) ? $purchaseId : null;
                                                    $purchaseSentBase         = VW::PRC.'.sent';
                                                    $purchaseSentKebab        = Str::kebab($purchaseSentBase);
                                                    $purchaseSentResolved     = Route::has($purchaseSentBase) ? $purchaseSentBase : (Route::has($purchaseSentKebab) ? $purchaseSentKebab : null);
                                                    $purchaseSentParams       = $purchaseIdVal ? [$purchaseIdVal] : ['#'];
                                                    $purchaseSentUrl          = ($purchaseSentResolved && $purchaseIdVal) ? route($purchaseSentResolved, $purchaseSentParams) : '#';
                                                    $purchaseSentGuardMsg     = Utility::fetchLinkMessage($lang, VW::PRC, 'sent_purchase_route_unavailable') ?? 'Sent purchase route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <a href="{{ $purchaseSentUrl }}"
                                                class="{{ VC::BT_SM }} btn-warning mark-sent-purchase"
                                                data-bs-toggle="tooltip"
                                                data-original-title="{{ __('Mark Sent') }}"
                                                data-url="{{ $purchaseSentUrl }}"
                                                data-guard-msg="{{ $purchaseSentGuardMsg }}"
                                                data-sv-localized="true">
                                                    <i class="ti ti-send {{ VC::MR2 }}"></i>{{ __('Send') }}
                                                </a>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script src="{{ asset('assets/js/routes/purchases/sent.js') }}" defer></script>
                                                @endpush
                                            @endcan
                                        @endif
                                    </div>
                                    <div class="{{ VC::CM6 }} {{ VC::CL4 }} col-xl-4">
                                        <div class="timeline-icons"><span class="timeline-dots"></span><i class="ti ti-report-money text-info"></i></div>
                                        <h6 class="text-info {{ VC::MY3 }}">{{ __('Get Paid') }}</h6>
                                        <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB3 }}">{{ __('Status') }} : {{ __('Awaiting payment') }}</p>
                                        @if($purchaseStatus !== 0)
                                            @can('create payment purchase')
                                                @php
                                                    $purchaseIdVal        = isset($purchaseId) ? $purchaseId : null;
                                                    $paymentBase          = VW::PRC.'.payment';
                                                    $paymentKebab         = Str::kebab($paymentBase);
                                                    $paymentResolved      = Route::has($paymentBase) ? $paymentBase : (Route::has($paymentKebab) ? $paymentKebab : null);
                                                    $paymentParams        = $purchaseIdVal ? [$purchaseIdVal] : ['#'];
                                                    $purchasePaymentUrl   = ($paymentResolved && $purchaseIdVal) ? route($paymentResolved, $paymentParams) : '#';
                                                    $purchasePaymentMsg   = Utility::fetchLinkMessage($lang, VW::PRC, 'add_payment_purchase_route_unavailable') ?? 'Add payment for purchase route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <a href="{{ $purchasePaymentUrl }}"
                                                data-url="{{ $purchasePaymentUrl }}"
                                                data-ajax-popup="true"
                                                data-title="{{ __('Add Payment') }}"
                                                class="{{ VC::BT_SM }} btn-info add-purchase-payment"
                                                data-original-title="{{ __('Add Payment') }}"
                                                data-guard-msg="{{ $purchasePaymentMsg }}"
                                                data-sv-localized="true">
                                                    <i class="ti ti-report-money {{ VC::MR2 }}"></i>{{ __('Add Payment') }}
                                                </a>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script src="{{ asset('assets/js/routes/purchases/payment.js') }}" defer></script>
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
        @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
            @if($purchaseStatus !== 0)
                <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }} {{ VC::MB3 }}">
                    <div class="{{ VC::CM12 }} {{ VC::DFL_AIC }} {{ VC::JCB }} justify-content-md-end">
                        @php
                            $purchaseIdVal               = isset($purchaseId) ? $purchaseId : null;
                            $resentBase                  = VW::PRC.'.resent';
                            $resentKebab                 = Str::kebab($resentBase);
                            $resentResolved              = Route::has($resentBase) ? $resentBase : (Route::has($resentKebab) ? $resentKebab : null);
                            $resentParams                = $purchaseIdVal ? [$purchaseIdVal] : ['#'];
                            $purchaseResentUrl           = ($resentResolved && $purchaseIdVal) ? route($resentResolved, $resentParams) : '#';
                            $purchaseResentGuardMsg      = Utility::fetchLinkMessage($lang, VW::PRC, 'resent_purchase_route_unavailable') ?? 'Resend purchase route is unavailable. Please contact technical support or your domain administrator.';
                            $encId                       = $purchaseIdVal ? Crypt::encrypt($purchaseIdVal) : null;
                            $pdfBase                     = VW::PRC.'.pdf';
                            $pdfKebab                    = Str::kebab($pdfBase);
                            $pdfResolved                 = Route::has($pdfBase) ? $pdfBase : (Route::has($pdfKebab) ? $pdfKebab : null);
                            $pdfParams                   = $encId ? [$encId] : ['#'];
                            $purchasePdfUrl              = ($pdfResolved && $encId) ? route($pdfResolved, $pdfParams) : '#';
                            $purchasePdfGuardMsg         = Utility::fetchLinkMessage($lang, VW::PRC, 'pdf_purchase_route_unavailable') ?? 'PDF purchase route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <div class="all-button-box mx-2">
                            <a href="{{ $purchaseResentUrl }}"
                            class="{{ VC::BT_SM_PM }} resent-purchase"
                            data-url="{{ $purchaseResentUrl }}"
                            data-guard-msg="{{ $purchaseResentGuardMsg }}"
                            data-sv-localized="true">{{ __('Resend Purchase') }}</a>
                        </div>
                        <div class="all-button-box">
                            <a href="{{ $purchasePdfUrl }}"
                            target="_blank"
                            class="{{ VC::BT_SM_PM }} purchase-pdf"
                            data-url="{{ $purchasePdfUrl }}"
                            data-guard-msg="{{ $purchasePdfGuardMsg }}"
                            data-sv-localized="true">{{ __('Download') }}</a>
                        </div>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script src="{{ asset('assets/js/routes/purchases/resent.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/purchases/pdf.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            @endif
        @endif
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_MT }}">
                        <div class="invoice">
                            <div class="invoice-print">
                                <div class="{{ VC::RW }} invoice-title mt-2">
                                    <div class="col-xs-12 col-sm-12 col-nd-6 {{ VC::CL6 }} {{ VC::C12 }}"><h4>{{ __('Purchase') }}</h4></div>
                                    @php
                                        $pnum = $user?->purchaseNumberFormat(data_get($purchase,'purchase_id')) ?? __('No purchase number available');
                                    @endphp
                                    <div class="col-xs-12 col-sm-12 col-nd-6 {{ VC::CL6 }} {{ VC::C12 }} text-end"><h4 class="invoice-number">{{ $pnum }}</h4></div>
                                    <div class="{{ VC::C12 }}"><hr></div>
                                </div>
                                <div class="{{ VC::RW }}">
                                    <div class="col text-end">
                                        <div class="{{ VC::DFL_AIC }} {{ VC::JCE }}">
                                            <div class="me-4">
                                                <small><strong>{{ __('Issue Date') }} :</strong><br>{{ $fmtDate(data_get($purchase,'purchase_date'), __('No issue date available')) }}<br><br></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::RW }}">
                                    @php
                                        $vatSwitch = (is_array($settings ?? null) || $settings instanceof \ArrayAccess) ? (data_get($settings,'vat_gst_number_switch') === 'on') : false;
                                        $b = $vendor ?? null;
                                        $s = $vendor ?? null;
                                        $bName = data_get($b,'billing_name');
                                        $bAddr = data_get($b,'billing_address');
                                        $bCity = data_get($b,'billing_city');
                                        $bState = data_get($b,'billing_state');
                                        $bZip = data_get($b,'billing_zip');
                                        $bCountry = data_get($b,'billing_country');
                                        $bPhone = data_get($b,'billing_phone');
                                        $bTax = data_get($b,'tax_number');
                                        $sName = data_get($s,'shipping_name');
                                        $sAddr = data_get($s,'shipping_address');
                                        $sCity = data_get($s,'shipping_city');
                                        $sState = data_get($s,'shipping_state');
                                        $sZip = data_get($s,'shipping_zip');
                                        $sCountry = data_get($s,'shipping_country');
                                        $sPhone = data_get($s,'shipping_phone');
                                    @endphp
                                    <div class="col">
                                        <small class="font-style">
                                            <strong>{{ __('Billed To') }} :</strong><br>
                                            @if(!empty($bName))
                                                {{ $bName }}<br>
                                                {{ $bAddr ?? __('No billing address available') }}<br>
                                                {{ $bCity ?? __('No billing city available') }}<br>
                                                {{ $bState ?? __('No billing state available') }}<br>
                                                {{ $bZip ?? __('No billing zip available') }}<br>
                                                {{ $bCountry ?? __('No billing country available') }}<br>
                                                {{ $bPhone ?? '' }}<br>
                                                @if($vatSwitch)
                                                    <strong>{{ __('Tax Number ') }} : </strong>{{ $bTax ?? '-' }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </small>
                                    </div>
                                    @if(App\Models\Utility::getValByName('shipping_display')=='on')
                                        <div class="col">
                                            <small>
                                                <strong>{{ __('Shipped To') }} :</strong><br>
                                                @if(!empty($sName))
                                                    {{ $sName }}<br>
                                                    {{ $sAddr ?? __('No shipping address available') }}<br>
                                                    {{ $sCity ?? __('No shipping city available') }}<br>
                                                    {{ $sState ?? __('No shipping state available') }}<br>
                                                    {{ $sZip ?? __('No shipping zip available') }}<br>
                                                    {{ $sCountry ?? __('No shipping country available') }}<br>
                                                    {{ $sPhone ?? __('No shipping phone available') }}<br>
                                                @else
                                                    -
                                                @endif
                                            </small>
                                        </div>
                                    @endif
                                    <div class="col">
                                        @php
                                            $purchaseIdVal          = isset($purchaseId) ? $purchaseId : null;
                                            $encId                  = $purchaseIdVal ? Crypt::encrypt($purchaseIdVal) : null;
                                            $copyLinkBase           = VW::PRC.'.link.copy';
                                            $copyLinkKebab          = Str::kebab($copyLinkBase);
                                            $copyLinkResolved       = Route::has($copyLinkBase) ? $copyLinkBase : (Route::has($copyLinkKebab) ? $copyLinkKebab : null);
                                            $copyLinkParams         = $encId ? [$encId] : ['#'];
                                            $purchaseCopyLinkUrl    = ($copyLinkResolved && $encId) ? route($copyLinkResolved, $copyLinkParams) : '#';
                                            $purchaseCopyLinkMsg    = Utility::fetchLinkMessage($lang, VW::PRC, 'copy_link_purchase_route_unavailable') ?? 'Copy link for purchase route is unavailable. Please contact technical support or your domain administrator.';
                                            $qrElId                 = 'purchase-copy-link-qrcode';
                                        @endphp
                                        <div id="{{ $qrElId }}" data-url="{{ $purchaseCopyLinkUrl }}" data-guard-msg="{{ $purchaseCopyLinkMsg }}" data-sv-localized="true">
                                            {!! DNS2D::getBarcodeHTML($purchaseCopyLinkUrl, 'QRCODE', 2, 2) !!}
                                        </div>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    try {
                                                        const el = document.getElementById('{{ $qrElId }}');
                                                        if (!el) return;
                                                        const flag = 'data-route-check-listener';
                                                        if (el.hasAttribute(flag) && el.getAttribute(flag) === 'true') return;
                                                        el.setAttribute(flag, 'true');
                                                        const url = el.getAttribute('data-url') || '#';
                                                        if (url !== '#') return;
                                                        const msg = el.getAttribute('data-guard-msg') || 'Copy link for purchase route is unavailable. Please contact technical support or your domain administrator.';
                                                        const linkEl = document.querySelector('link[href*="bootstrap"]');
                                                        const hasBootstrapToast = (typeof window !== 'undefined' && window.bootstrap && typeof window.bootstrap.Toast === 'function');
                                                        let container = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            container.className = 'position-fixed top-0 end-0 p-3';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (linkEl && hasBootstrapToast) {
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
                                                            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                                                            toast.addEventListener('hidden.bs.toast', function() { try { toast.remove(); } catch (_) {} });
                                                            inst.show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        el.setAttribute('data-failed-route', 'true');
                                                    } catch (_) {}
                                                })();
                                            </script>
                                        @endpush
                                    </div>
                                </div>
                                <div class="{{ VC::RW }} {{ VC::MT3 }}">
                                    <div class="col">
                                        @php
                                            $statusClasses = [0=>'bg-secondary',1=>'bg-warning',2=>'bg-danger',3=>'bg-info',4=>'bg-success'];
                                            $statusIdx = $purchaseStatus;
                                            $badgeClass = $statusClasses[$statusIdx] ?? 'bg-secondary';
                                            $statusText = __(Purchase::$statuses[$statusIdx] ?? 'Unknown status');
                                        @endphp
                                        <small><strong>{{ __('Status') }} :</strong><br><span class="{{ VC::BDG }} {{ $badgeClass }} p-2 {{ VC::PX3 }} rounded">{{ $statusText }}</span></small>
                                    </div>
                                </div>
                                <div class="{{ VC::RW }} {{ VC::MT4 }}">
                                    <div class="{{ VC::CM12 }}">
                                        <div class="font-bold mb-2">{{ __('Product Summary') }}</div>
                                        <small class="mb-2">{{ __('All items here cannot be deleted.') }}</small>
                                        <div class="table-responsive {{ VC::MT3 }}">
                                            <table class="{{ VC::TB }}">
                                                <thead>
                                                    <tr>
                                                        <th class="text-dark" data-width="40">#</th>
                                                        <th class="text-dark">{{ __('Product') }}</th>
                                                        <th class="text-dark">{{ __('Quantity') }}</th>
                                                        <th class="text-dark">{{ __('Rate') }}</th>
                                                        <th class="text-dark">{{ __('Discount') }}</th>
                                                        <th class="text-dark">{{ __('Tax') }}</th>
                                                        <th class="text-dark">{{ __('Description') }}</th>
                                                        <th class="text-end text-dark" width="12%">{{ __('Price') }}<br><small class="text-danger font-weight-bold">{{ __('after tax & discount') }}</small></th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                @php
                                                    $items = is_iterable($iteams ?? null) ? $iteams : [];
                                                    $totalQuantity=0; $totalRate=0; $totalTaxPrice=0; $totalDiscount=0; $taxesData=[];
                                                @endphp
                                                @foreach($items as $key => $item)
                                                    @php
                                                        $qty = (float)(data_get($item,'quantity') ?? 0);
                                                        $price = (float)(data_get($item,'price') ?? 0);
                                                        $disc = (float)(data_get($item,'discount') ?? 0);
                                                        $totalQuantity += $qty; $totalRate += $price; $totalDiscount += $disc;
                                                        $taxes = !empty(data_get($item,'tax')) ? App\Models\Utility::tax(data_get($item,'tax')) : [];
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>{{ data_get($item,'product.name') ?? __('No product name available') }}</td>
                                                        <td>{{ $qty }}</td>
                                                        <td>{{ $user?->priceFormat($price) }}</td>
                                                        <td>{{ $user?->priceFormat($disc) }}</td>
                                                        <td>
                                                            @if(!empty($taxes))
                                                                <table>
                                                                    @php $rowTaxPrice=0; @endphp
                                                                    @foreach($taxes as $tax)
                                                                        @php
                                                                            $rate = (float)($tax->rate ?? 0);
                                                                            $tprice = App\Models\Utility::taxRate($rate,$price,$qty,$disc);
                                                                            $rowTaxPrice += $tprice;
                                                                            $tname = $tax->name ?? __('Tax');
                                                                            $taxesData[$tname] = ($taxesData[$tname] ?? 0) + $tprice;
                                                                        @endphp
                                                                        <tr><td>{{ $tname.' ('.$rate.'%)' }}</td><td>{{ $user?->priceFormat($tprice) }}</td></tr>
                                                                    @endforeach
                                                                </table>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>{{ data_get($item,'description') ?? '-' }}</td>
                                                        @php $lineTotal = ($price * $qty - $disc) + ($rowTaxPrice ?? 0); @endphp
                                                        <td class="text-end">{{ $user?->priceFormat($lineTotal) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tfoot>
                                                    <tr>
                                                        <td></td>
                                                        <td><b>{{ __('Total') }}</b></td>
                                                        <td><b>{{ $totalQuantity }}</b></td>
                                                        <td><b>{{ $user?->priceFormat($totalRate) }}</b></td>
                                                        <td><b>{{ $user?->priceFormat($totalDiscount) }}</b></td>
                                                        <td><b>{{ $user?->priceFormat($totalTaxPrice) }}</b></td>
                                                    </tr>
                                                    <tr>
                                                        @php
                                                            $subTotal = method_exists($purchase,'getSubTotal') ? $purchase->getSubTotal() : 0;
                                                            $totalDiscountVal = method_exists($purchase,'getTotalDiscount') ? $purchase->getTotalDiscount() : 0;
                                                            $grandTotal = method_exists($purchase,'getTotal') ? $purchase->getTotal() : 0;
                                                            $due = method_exists($purchase,'getDue') ? $purchase->getDue() : 0;
                                                            $paid = $grandTotal - $due;
                                                        @endphp
                                                        <td colspan="6"></td>
                                                        <td class="text-end"><b>{{ __('Sub Total') }}</b></td>
                                                        <td class="text-end">{{ $user?->priceFormat($subTotal) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="text-end"><b>{{ __('Discount') }}</b></td>
                                                        <td class="text-end">{{ $user?->priceFormat($totalDiscountVal) }}</td>
                                                    </tr>
                                                    @if(!empty($taxesData))
                                                        @foreach($taxesData as $taxName => $taxPrice)
                                                            <tr>
                                                                <td colspan="6"></td>
                                                                <td class="text-end"><b>{{ $taxName }}</b></td>
                                                                <td class="text-end">{{ $user?->priceFormat($taxPrice) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="blue-text text-end"><b>{{ __('Total') }}</b></td>
                                                        <td class="blue-text text-end">{{ $user?->priceFormat($grandTotal) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="text-end"><b>{{ __('Paid') }}</b></td>
                                                        <td class="text-end">{{ $user?->priceFormat($paid) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="text-end"><b>{{ __('Due') }}</b></td>
                                                        <td class="text-end">{{ $user?->priceFormat($due) }}</td>
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
                    <div class="card-body table-border-style">
                        <h5 class=" d-inline-block mb-5">{{ __('Payment Summary') }}</h5>
                        <div class="table-responsive">
                            <table class="{{ VC::TB }}">
                                <thead>
                                    <tr>
                                        <th class="text-dark">{{ __('Payment Receipt') }}</th>
                                        <th class="text-dark">{{ __('Date') }}</th>
                                        <th class="text-dark">{{ __('Amount') }}</th>
                                        <th class="text-dark">{{ __('Account') }}</th>
                                        <th class="text-dark">{{ __('Reference') }}</th>
                                        <th class="text-dark">{{ __('Description') }}</th>
                                        @can('delete payment purchase')
                                            <th class="text-dark">{{ __('Action') }}</th>
                                        @endcan
                                    </tr>
                                </thead>
                                @php
                                    $payments = is_iterable(data_get($purchase,'payments')) ? data_get($purchase,'payments') : [];
                                @endphp
                                @forelse($payments as $key => $payment)
                                    @php
                                        $receipt = data_get($payment,'add_receipt');
                                        $pDate = $fmtDate(data_get($payment,'date'), __('No date available'));
                                        $pAmount = $fmtMoney(data_get($payment,'amount') ?? 0);
                                        $accName = trim(((data_get($payment,'bankAccount.bank_name') ?? '').' '.(data_get($payment,'bankAccount.holder_name') ?? '')));
                                        $ref = data_get($payment,'reference') ?? '-';
                                        $desc = data_get($payment,'description') ?? '-';
                                        $paymentId = data_get($payment,'id');
                                    @endphp
                                    <tr>
                                        <td>
                                            @if(!empty($receipt))
                                                <a href="{{ asset(Storage::url('uploads/payment')).'/'.$receipt }}" download class="{{ VC::BT_SM }} btn-secondary btn-icon rounded-pill" target="_blank"><span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span></a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $pDate }}</td>
                                        <td>{{ $pAmount }}</td>
                                        <td>{{ $accName ?: '-' }}</td>
                                        <td>{{ $ref }}</td>
                                        <td>{{ $desc }}</td>
                                        @can('delete payment purchase')
                                            <td class="text-dark">
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    @php
                                                        $purchaseIdVal                = isset($purchaseId) ? $purchaseId : null;
                                                        $paymentIdVal                 = isset($paymentId) ? $paymentId : null;
                                                        $deleteFormId                 = 'delete-form-'.($paymentIdVal ?? 'x');
                                                        $paymentDestroyBase           = VW::PRC.'.payment.destroy';
                                                        $paymentDestroyKebab          = Str::kebab($paymentDestroyBase);
                                                        $paymentDestroyResolved       = Route::has($paymentDestroyBase) ? $paymentDestroyBase : (Route::has($paymentDestroyKebab) ? $paymentDestroyKebab : null);
                                                        $paymentDestroyParams         = ($purchaseIdVal && $paymentIdVal) ? [$purchaseIdVal, $paymentIdVal] : ['#'];
                                                        $paymentDestroyUrl            = ($paymentDestroyResolved && $purchaseIdVal && $paymentIdVal) ? route($paymentDestroyResolved, $paymentDestroyParams) : '#';
                                                        $paymentDestroyGuardMsg       = Utility::fetchLinkMessage($lang, VW::PRC, 'destroy_purchase_payment_route_unavailable') ?? 'Destroy purchase payment route is unavailable. Please contact technical support or your domain administrator.';
                                                        $confirmTitle                 = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                                                        $confirmBody                  = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                                                    @endphp
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method'            => 'POST',
                                                        'url'               => $paymentDestroyUrl,
                                                        'id'                => $deleteFormId,
                                                        'data-url'          => $paymentDestroyUrl,
                                                        'data-guard-msg'    => $paymentDestroyGuardMsg,
                                                        'data-sv-localized' => 'true',
                                                    ]) !!}
                                                        <a href="{{ $paymentDestroyUrl }}"
                                                        class="{{ VC::BT_SM_CT_PR }} delete-purchase-payment"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                        data-original-title="{{ __('Delete') }}"
                                                        data-url="{{ $paymentDestroyUrl }}"
                                                        data-guard-msg="{{ $paymentDestroyGuardMsg }}"
                                                        data-sv-localized="true"
                                                        data-confirm="{{ __($confirmTitle) }}|{{ __($confirmBody) }}"
                                                        data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script src="{{ asset('assets/js/routes/purchases/delete.js') }}" defer></script>
                                                    @endpush
                                                </div>
                                            </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-dark"><p>{{ __('No Data Found') }}</p></td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
@else
    <div class="alert alert-warning">{{__('No purchase found')}}</div>
@endif