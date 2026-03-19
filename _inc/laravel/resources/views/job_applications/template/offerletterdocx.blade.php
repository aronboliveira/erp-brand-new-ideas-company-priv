
@php
    try {
} catch (\Throwable $e) {
        \Log::error('job_applications/template/offerletterdocx — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::CTC)
@section(YieldingConstants::CTC_PG_TTL)
    {{ __('Offer Letter') }}
@endsection
@section(YieldingConstants::CTC_CTT)
    <div class="row" >
        <div class="{{ VC::CL10 }}">
            <div class="{{ ViewClassNamesConstants::CT }}">
                <div>
                    <div class="card mt-5" id="printTable" style="margin-left: 180px;margin-right: -57px;">
                        <div class="{{ VC::CD_BD }}" id="exportContent">
                            <div class="row invoice-title {{ VC::MT2 }}">
                                <div class="{{ VC::CXS12 }} {{ VC::CS12 }} col-nd-6 {{ VC::CL6 }} {{ VC::C12 }}">
                                    {{-- <img  src="{{$img}}" style="max-width: 150px;"/> --}}
                                </div>
                                <p data-v-f2a183a6="">
                                    {{-- @dd($Offerletter) --}}
                                    @if(!empty($Offerletter) && !isset($Offerletter->content))
                                        <div>{!!$Offerletter->content!!}</div>
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
    <script async src="{{ asset('assets/js/routes/jobs/applications/lang/doc.js') }}"></script>
    <script defer>
        (function () {
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const dataSvLocalized = "data-sv-localized";
        const dataErrArmed = "data-doc-error-armed";
        const dataBound = "data-doc-export-bound";
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
        const showToast = message => {
            if (hasBS()) {
            let t = qs("#np-toast");
            if (!t) {
                const container = ensureToastContainer();
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
                showToast(msg);
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
            el.getAttribute(dataSvLocalized) === "true" ||
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
        const triggerDownload = (html, filename) => {
            try {
            let a = qs("#np-doc-download");
            if (!a) {
                a = document.createElement("a");
                a.id = "np-doc-download";
                a.style.position = "fixed";
                a.style.left = "-9999px";
                document.body.appendChild(a);
            }
            let ok = false;
            try {
                const blob = new Blob(["\ufeff", html], { type: "application/msword" });
                if (window.navigator && "msSaveOrOpenBlob" in window.navigator) {
                window.navigator.msSaveOrOpenBlob(blob, filename);
                ok = true;
                } else {
                const url = URL.createObjectURL(blob);
                a.href = url;
                a.download = filename;
                a.click();
                setTimeout(function () {
                    try {
                    URL.revokeObjectURL(url);
                    } catch (_) {}
                }, 1200);
                ok = true;
                }
            } catch (_) {}
            if (!ok) {
                try {
                const url =
                    "data:application/vnd.ms-word;charset=utf-8," +
                    encodeURIComponent(html);
                a.href = url;
                a.download = filename;
                a.click();
                ok = true;
                } catch (_) {}
            }
            if (!ok) {
                schedulePointerupError(localize(document.body, "export_unavailable"));
            }
            } catch (_) {
            schedulePointerupError(localize(document.body, "export_unavailable"));
            }
        };
        const run = () => {
            const body = document.body;
            if (body.getAttribute(dataBound) === "true") return;
            body.setAttribute(dataBound, "true");
            let filename = "{{$name->name}}";
            filename = (filename ?? "").toString().trim();
            filename = filename ? filename + ".doc" : "document.doc";
            const elementId = "exportContent";
            const el = document.getElementById(elementId);
            if (!el) {
            schedulePointerupError(localize(body, "element_unavailable"));
            return;
            }
            const preHtml =
            "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'><title>{{ __('Export HTML To Doc') }}</title></head><body>";
            const postHtml = "</body></html>";
            const html = preHtml + (el.innerHTML ?? "") + postHtml;
            triggerDownload(html, filename);
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
        const cleanMo = new MutationObserver(function () {
            if (!qs("#exportContent")) {
            document.body.removeAttribute(dataBound);
            }
        });
        cleanMo.observe(document.documentElement, { childList: true, subtree: true });
        window.closeScript = closeScript;
        })();
    </script>
@endpush
