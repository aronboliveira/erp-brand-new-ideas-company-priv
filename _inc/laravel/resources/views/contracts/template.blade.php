
@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        $logo = Utility::getFile('uploads/logo/');
        $dark_logo   = Utility::getValByName('dark_logo');
        $img = asset($logo . '/' . (isset($dark_logo) && !empty($dark_logo) ? $dark_logo : SettingsConstants::CPN_LG_DK_DEF));
        $settings = Utility::settings();
    } catch (\Throwable $e) {
        \Log::error('contracts/template — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::CTC)
@if(!empty($contract) && isset($contract->id))
    @section(YieldingConstants::CTC_CTT)
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CL10 }}">
                <div class="{{ VC::CT }}">
                    <div>
                        <div class="{{ VC::CD }} mt-5" id="printTable" style="margin-left: 180px;margin-right: -57px;">
                            <div class="{{ VC::CD_BD }}" id="boxes">
                                @php
                                    try {
                                        $hasPriceFormat = method_exists($user,'priceFormat');
                                        $hasDateFormat = method_exists($user,'dateFormat');
                                        $hasContractNumberFormat = method_exists($user,'contractNumberFormat');
                                        $contractNumber = (isset($contract->id) && $hasContractNumberFormat) ? ($user?->contractNumberFormat($contract->id) ?? __('Failed to format contract number')) : __('No contract number available');
                                        $typeName = data_get($contract,'types.name') ?: __('No contract type available');
                                        $valueText = isset($contract->value) && is_numeric($contract->value) ? ($hasPriceFormat ? ($user?->priceFormat($contract->value) ?? __('Failed to format contract value')) : __('Failed to format contract value')) : __('No contract value available');
                                        $startDateText = isset($contract->start_date) ? ($hasDateFormat ? ($user?->dateFormat($contract->start_date) ?? __('Failed to format start date')) : __('Failed to format start date')) : __('No start date available');
                                        $endDateText = isset($contract->end_date) ? ($hasDateFormat ? ($user?->dateFormat($contract->end_date) ?? __('Failed to format end date')) : __('Failed to format end date')) : __('No end date available');
                                        $logoSrc = !empty($img) ? $img : '';
                                        $descHtml = !empty($contract->description) ? $contract->description : e(__('No description available'));
                                        $contractDescHtml = !empty($contract->contract_description) ? $contract->contract_description : e(__('No contract description available'));
                                        $companySig = !empty($contract->company_signature) ? $contract->company_signature : null;
                                        $clientSig = !empty($contract->client_signature) ? $contract->client_signature : null;
                                    } catch (\Throwable $e) {
                                        \Log::error('contracts/template — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <div class="{{ VC::RW }} invoice-title mt-2">
                                    <div class="{{ VC::CXS12 }} {{ VC::CS12 }} {{ VC::CM6 }} {{ VC::CL6 }} {{ VC::C12 }}">
                                        <img src="{{ $logoSrc }}" alt="{{ $logoSrc ? __('Company Logo') : __('No logo available') }}" style="max-width: 150px;"/>
                                    </div>
                                    <div class="{{ VC::CXS12 }} {{ VC::CS12 }} {{ VC::CM6 }} {{ VC::CL6 }} {{ VC::C12 }} text-end">
                                        <h3 class="invoice-number">{{ $contractNumber }}</h3>
                                    </div>
                                </div>
                                <div class="{{ VC::R_ALC_M4 }}">
                                    <div class="col-sm-6 mb-3 mb-sm-0 {{ VC::MT3 }}">
                                        <div class="col-lg-12 col-md-8 {{ VC::MB3 }}">
                                            <h6 class="d-inline-block m-0 d-print-none">{{ __('Contract Type  :') }}</h6>
                                            <span class="{{ VC::CM8 }}"><span class="text-md">{{ $typeName }}</span></span>
                                        </div>
                                        <div class="{{ VC::CL6 }} {{ VC::CM8 }}">
                                            <h6 class="d-inline-block m-0 d-print-none">{{ __('Contract Value   :') }}</h6>
                                            <span class="{{ VC::CM8 }}"><span class="text-md">{{ $valueText }}</span></span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CS6 }} text-sm-end">
                                        <div>
                                            <div class="{{ VC::FEND }}">
                                                <div>
                                                    <h6 class="d-inline-block m-0 d-print-none">{{ __('Start Date   :') }}</h6>
                                                    <span class="{{ VC::CM8 }}"><span class="text-md">{{ $startDateText }}</span></span>
                                                </div>
                                                <div class="{{ VC::MT3 }}">
                                                    <h6 class="d-inline-block m-0 d-print-none">{{ __('End Date   :') }}</h6>
                                                    <span class="{{ VC::CM8 }}"><span class="text-md">{{ $endDateText }}</span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p data-v-f2a183a6="">
                                    <div>{!! $descHtml !!}</div>
                                    <br>
                                    <div>{!! $contractDescHtml !!}</div>
                                </p>
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::C6 }}">
                                        <div>
                                            @if($companySig)
                                                <img width="200px" src="{{ $companySig }}" alt="{{ __('Company Signature') }}">
                                            @else
                                                <span class="{{ VC::TXT_MT }}">{{ __('No company signature available') }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <h5 class="mt-auto">{{ __('Company Signature') }}</h5>
                                        </div>
                                    </div>
                                    <div class="{{ VC::C6 }} {{ VC::TX_END }}">
                                        @if($clientSig)
                                            <img width="150px" src="{{ $clientSig }}" alt="{{ __('Client Signature') }}">
                                        @else
                                            <span class="{{ VC::TXT_MT }}">{{ __('No client signature available') }}</span>
                                        @endif
                                        <h5 class="mt-auto">{{ __('Client Signature') }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @push(StacksConstants::CTC_SCR_PG)
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
        <script async src="{{ asset('assets/js/routes/contracts/lang/pdf.js') }}"></script>
        <script defer>
            (function () {
            const $ = window.jQuery;
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataSvLocalized = "data-sv-localized";
            const dataErrArmed = "data-pdf-error-armed";
            const dataPdfBound = "data-pdf-bound";
            const qs = (s, r = document) => r.querySelector(s);
            const hasBS = () =>
                !!(
                qs('link[rel="stylesheet"][href*="bootstrap"]') ||
                qs('link[href*="bootstrap"]')
                ) && !!(window.bootstrap && window.bootstrap.Toast);
            const ensureToastContainer = () => {
                let c = qs("#np-toast-container");
                if (c) return c;
                c = document.createElement("div");
                c.id = "np-toast-container";
                c.setAttribute("aria-live", "polite");
                c.setAttribute("aria-atomic", "true");
                c.style.position = "fixed";
                c.style.top = "1rem";
                c.style.right = "1rem";
                document.body.appendChild(c);
                return c;
            };
            const showErrorNow = message => {
                if (hasBS()) {
                const container = ensureToastContainer();
                let t = qs("#np-toast", container);
                if (!t) {
                    t = document.createElement("div");
                    t.id = "np-toast";
                    t.className = "toast";
                    t.setAttribute("role", "alert");
                    t.setAttribute("aria-live", "assertive");
                    t.setAttribute("aria-atomic", "true");
                    t.innerHTML =
                    '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                    container.appendChild(t);
                }
                const body = t.querySelector(".toast-body");
                if (body) body.textContent = message ?? errFb;
                try {
                    new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
                } catch (_) {
                    alert(message ?? errFb);
                }
                } else {
                alert(message ?? errFb);
                }
            };
            const schedulePointerupError = msg => {
                const host = document.body;
                if (!host || host.getAttribute(dataErrArmed) === "true") return;
                host.setAttribute(dataErrArmed, "true");
                const once = () => {
                try {
                    showErrorNow(msg);
                } finally {
                    host.removeAttribute(dataErrArmed);
                }
                };
                document.addEventListener("pointerup", once, { once: true });
                const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(host)) {
                    document.removeEventListener("pointerup", once);
                    o.disconnect();
                }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
            };
            const localize = (el, key) => {
                let msg = errFb;
                if (
                el?.getAttribute?.(dataSvLocalized) === "true" ||
                el?.getAttribute?.(dataClientLocalized) === "true"
                ) {
                msg = el.getAttribute(dataGuardMsg) || errFb;
                } else {
                let lang = (
                    window.sessionStorage.getItem("erp-np-lang") ||
                    document.documentElement.lang ||
                    "en"
                )
                    .toLowerCase()
                    .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                const msgKey = key;
                msg =
                    window.translations?.[lang]?.[msgKey] ||
                    el?.getAttribute?.(dataGuardMsg) ||
                    window.translations?.["en"]?.[msgKey] ||
                    errFb;
                if (msg !== errFb && el) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, "true");
                }
                }
                return msg;
            };
            const closeScript = () => {
                try {
                setTimeout(function () {
                    try {
                    window.open(window.location, "_self").close();
                    } catch (_) {
                    schedulePointerupError(localize(document.body, "close_unavailable"));
                    }
                }, 1000);
                } catch (_) {
                schedulePointerupError(localize(document.body, "close_unavailable"));
                }
            };
            const bind = () => {
                const body = document.body;
                if (body.getAttribute(dataPdfBound) === "true") return;
                body.setAttribute(dataPdfBound, "true");
                const run = () => {
                try {
                    const element = document.getElementById("boxes");
                    if (!element) {
                    schedulePointerupError(localize(body, "pdf_unavailable"));
                    return;
                    }
                    if (
                    typeof window.html2pdf !== "function" &&
                    typeof window.html2pdf !== "object"
                    ) {
                    try {
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) console.error("html2pdf unavailable");
                    } catch (_) {}
                    schedulePointerupError(localize(body, "pdf_unavailable"));
                    return;
                    }
                    const opt = {
                    filename: "{{Utility::contractNumberFormat($contract->id)}}",
                    image: { type: "jpeg", quality: 1 },
                    html2canvas: { scale: 4, dpi: 72, letterRendering: true },
                    jsPDF: { unit: "in", format: "A4" },
                    };
                    try {
                    window
                        .html2pdf()
                        .set(opt)
                        .from(element)
                        .save()
                        .then(closeScript)
                        .catch(function () {
                        schedulePointerupError(localize(body, "pdf_unavailable"));
                        });
                    } catch (_) {
                    schedulePointerupError(localize(body, "pdf_unavailable"));
                    }
                } catch (_) {
                    schedulePointerupError(localize(body, "pdf_unavailable"));
                }
                };
                if ($ && $.fn && $(window)?.on) {
                $(window).on("load", run);
                } else {
                try {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("jQuery unavailable");
                } catch (_) {}
                if (document.readyState === "complete") {
                    run();
                } else {
                    window.addEventListener("load", run, { once: true });
                }
                }
                const mo = new MutationObserver(function () {
                if (!qs("#boxes")) {
                    body.removeAttribute(dataPdfBound);
                }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
            };
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", bind, { once: true });
            } else {
                bind();
            }
            })();
        </script>
    @endpush
@else
    <div class="{{ VC::ALT_DNG }}">{{ __('No contract found') }}</div>
@endif
