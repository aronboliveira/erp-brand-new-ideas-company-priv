@php
	try {
} catch (\Throwable $e) {
		\Log::error('employees/templates/exp_certificate_doc — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp
@extends(ExtendingLayoutsConstants::CTC)
@section(YieldingConstants::CTC_PG_TTL)
{{ __('Experience Certificate') }}
@endsection
@section(YieldingConstants::CTC_CTT)
<div class="row">
    <div class="{{ VC::CL10 }}">
        <div class="{{ ViewClassNamesConstants::CT }}">
            <div>
                <div class="card mt-5" id="printTable" style="margin-left: 180px;margin-right: -57px;">
                    <div class="{{ VC::CD_BD }}" id="exportContent">
                        <div class="row invoice-title {{ VC::MT2 }}">
                            <div class="{{ VC::CXS12 }} {{ VC::CS12 }} col-nd-6 {{ VC::CL6 }} {{ VC::C12 }}">
                                {{-- <img  src="{{--$img--}"}
                                {{-- style="max-width: 150px;"/> --}}
                            </div>
                            <p data-v-f2a183a6="">
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
    <script async src="{{ asset('assets/js/routes/employees/templates/experiences/lang/doc.js') }}"></script>
    <script defer>
        (function () {
            const $ = window.jQuery;
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataSvLocalized = "data-sv-localized";
            const dataErrArmed = "data-export-error-armed";
            const qs = (s, r = document) => r.querySelector(s);
            const hasBS = () =>
                !!(
                qs('link[rel="stylesheet"][href*="bootstrap"]') ||
                qs('link[href*="bootstrap"]')
                ) && !!(window.bootstrap && window.bootstrap.Toast);
            const ensureToastContainer = () => {
                let c = qs("#np-toast-container");
                if (c) return c;
                const d = document.createElement("div");
                d.id = "np-toast-container";
                d.setAttribute("aria-live", "polite");
                d.setAttribute("aria-atomic", "true");
                d.style.position = "fixed";
                d.style.top = "1rem";
                d.style.right = "1rem";
                document.body.appendChild(d);
                return d;
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
                    '<div class="toast-header"><strong class="me-auto">{{ __('Notice') }}</strong><button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div><div class="toast-body"></div>';
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
            const scheduleClickError = msg => {
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
                document.addEventListener("click", once, { once: true });
                const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(host)) {
                    document.removeEventListener("click", once);
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
                    scheduleClickError(localize(document.body, "close_unavailable"));
                    }
                }, 1000);
                } catch (_) {
                scheduleClickError(localize(document.body, "close_unavailable"));
                }
            };
            const triggerDownload = (html, filename) => {
                try {
                const blob = new Blob(["\ufeff", html], { type: "application/msword" });
                const url = URL.createObjectURL(blob);
                let a = qs("#np-doc-download");
                if (!a) {
                    a = document.createElement("a");
                    a.id = "np-doc-download";
                    a.style.position = "fixed";
                    a.style.left = "-9999px";
                    document.body.appendChild(a);
                }
                a.href = url;
                a.download = filename;
                a.click();
                setTimeout(function () {
                    try {
                    URL.revokeObjectURL(url);
                    } catch (_) {}
                }, 1000);
                } catch (_) {
                scheduleClickError(localize(document.body, "export_unavailable"));
                }
            };
            const run = () => {
                const body = document.body;
                const employeeName = "{{$employees->name}}" ?? "";
                const filename =
                employeeName && String(employeeName).trim()
                    ? String(employeeName).trim() + ".doc"
                    : "document.doc";
                const elementId = "exportContent";
                const el = document.getElementById(elementId);
                if (!el) {
                scheduleClickError(localize(body, "element_unavailable"));
                return;
                }
                const preHtml =
                "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'><title>{{ __('Export HTML To Doc') }}</title></head><body>";
                const postHtml = "</body></html>";
                const inner = el.innerHTML ?? "";
                const html = preHtml + inner + postHtml;
                triggerDownload(html, filename);
            };
            const bind = () => {
                if ($ && $.fn && $(window)?.on) {
                $(window).on("load", run);
                } else {
                if (!window.jQuery) {
                    try {
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) console.error("jQuery unavailable");
                    } catch (_) {}
                }
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
            const mo = new MutationObserver(function () {
                const a = qs("#np-doc-download");
                if (a && !document.body.contains(a)) {
                document.removeEventListener("click", showErrorNow);
                }
            });
            mo.observe(document.documentElement, { childList: true, subtree: true });
            window.closeScript = closeScript;
        })();
    </script>
@endpush
