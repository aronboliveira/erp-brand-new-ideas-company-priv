
@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        YieldingConstants,
        StacksConstants,
        ViewClassNamesConstants
    };
@endphp
@extends(ExtendingLayoutsConstants::CTC)
@section(YieldingConstants::CTC_PG_TTL)
    {{ __('Experience Certificate') }}
@endsection
@section(YieldingConstants::CTC_CTT)
    <div class="row">
        <div class="col-lg-10">
            <div class="{{ ViewClassNamesConstants::CT }}">
                <div>
                    <div class="card mt-5" id="printTable" style="margin-left: 180px;margin-right: -57px;">
                        <div class="card-body" id="boxes">
                            <div class="row invoice-title mt-2">
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 ">
                                    {{-- <img  src="{{$img}}" style="max-width: 150px;"/> --}}
                                </div>
                                <p data-v-f2a183a6="" >
                                    {{-- @dd($Offerletter) --}}
                                @if(!empty($experience_certificate) && isset($experience_certificate->content))
                                    <div>{!!$experience_certificate->content!!}</div>
                                @else
                                    <div>{{ __('No content available') }}</div>
                                @endif
                                            {{-- <br>
                                            <div>{!!$contract->contract_description!!}</div> --}}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push(StacksConstants::CTC_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/employees/templates/experiences/lang/pdf.js') }}"></script>
    <script defer>
        (function () {
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
                    '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
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
            const localize = function (el, msgKey) {
                let msg = errFb;
                if (
                el.getAttribute("data-sv-localized") === "true" ||
                el.getAttribute(dataClientLocalized) === "true"
                )
                msg = el.getAttribute(dataGuardMsg) || errFb;
                else {
                let lang = (
                    window.sessionStorage.getItem("erp-np-lang") ||
                    document.documentElement.lang ||
                    "en"
                )
                    .toLowerCase()
                    .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                const k = msgKey;
                msg =
                    window.translations?.[lang]?.[k] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.["en"]?.[k] ||
                    errFb;
                if (msg !== errFb) {
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
            const run = () => {
                const body = document.body;
                if (body.getAttribute(dataPdfBound) === "true") return;
                body.setAttribute(dataPdfBound, "true");
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
                    console.error("html2pdf unavailable");
                    } catch (_) {}
                    schedulePointerupError(localize(body, "pdf_unavailable"));
                    return;
                }
                const opt = {
                    filename: "{{$employees->name}}",
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
                schedulePointerupError(localize(document.body, "pdf_unavailable"));
                }
                const mo = new MutationObserver(function () {
                if (!qs("#boxes")) {
                    body.removeAttribute(dataPdfBound);
                }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
            };
            const bind = () => {
                const $ = window.jQuery;
                if ($ && $.fn && $(window)?.on) {
                $(window).on("load", run);
                } else {
                if (document.readyState === "complete") {
                    run();
                } else {
                    window.addEventListener("load", run, { once: true });
                }
                }
            };
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", bind, { once: true });
            } else {
                bind();
            }
        })();
    </script>
@endpush