/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/receivables/receipts/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */
(function () {
    const $ = window.jQuery;
    const qs = (s, r = document) => r.querySelector(s);
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", _dataSvLocalized = "data-sv-localized", dataErrGuard = "data-err-guard", dataFilterGuard = "data-filter-guard", dataPrintGuard = "data-print-guard";
    const ensureToastContainer = () => {
        const id = "np-toast-container", existing = qs("#" + id);
        if (existing)
            return existing;
        const c = document.createElement("div");
        c.id = id;
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        Object.assign(c.style, { position: "fixed", top: "1rem", right: "1rem" });
        document.body.appendChild(c);
        return c;
    };
    const showErrorNow = (message) => {
        const hasBootstrap = (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
            qs('link[href*="bootstrap"]')) &&
            window.bootstrap.Toast;
        if (hasBootstrap) {
            const container = ensureToastContainer();
            let t = qs("#np-toast", container);
            if (!t) {
                t = document.createElement("div");
                t.id = "np-toast";
                t.className = "toast";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    t.setAttribute(k, v);
                t.innerHTML =
                    '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
            }
            const body = qs(".toast-body", t);
            if (body)
                body.textContent = message ?? errFb;
            try {
                new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
            }
            catch (_) {
                alert(message ?? errFb);
            }
        }
        else {
            alert(message ?? errFb);
        }
    };
    const scheduleInteractiveError = (message, evt = "click") => {
        const host = document.body;
        if (!host || host.getAttribute(dataErrGuard) === "true")
            return;
        host.setAttribute(dataErrGuard, "true");
        const once = () => {
            try {
                showErrorNow(message);
            }
            finally {
                host.removeAttribute(dataErrGuard);
            }
        };
        document.addEventListener(evt, once, { once: true });
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(host)) {
                document.removeEventListener(evt, once);
                o.disconnect();
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    const getMsg = (el, key) => {
        const err = errFb;
        if (el.getAttribute("data-sv-localized") === "true" ||
            el.getAttribute(dataClientLocalized) === "true")
            return el.getAttribute(dataGuardMsg) || err;
        let lang = (window.sessionStorage.getItem("erp-np-lang") ??
            document.documentElement.lang ??
            "en")
            .toLowerCase()
            .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        const msgKey = key;
        const msg = window.translations?.[lang]?.[msgKey] ||
            el.getAttribute(dataGuardMsg) ||
            window.translations?.en?.[msgKey] ||
            err;
        if (el && msg !== err) {
            el.setAttribute(dataGuardMsg, msg);
            el.setAttribute(dataClientLocalized, "true");
        }
        return msg;
    };
    const saveAsPDF = () => {
        const area = document.getElementById("printableArea");
        if (!area) {
            scheduleInteractiveError(getMsg(document.body, "pdf_unavailable"));
            return;
        }
        const name = String((window.jQuery && $("#filename").val()) || "").trim() || "export";
        const opt = {
            margin: 0.3,
            filename: name,
            image: { type: "jpeg", quality: 1 },
            html2canvas: { scale: 4, dpi: 72, letterRendering: true },
            jsPDF: { unit: "in", format: "A2" },
        };
        try {
            if (typeof window.html2pdf !== "function") {
                try {
                    if (window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1")
                        console.error("html2pdf unavailable");
                }
                catch (_) {
                    console.error(`[pdf] Error:`, _);
                }
                scheduleInteractiveError(getMsg(area, "plugin_unavailable"));
                return;
            }
            window.html2pdf().set(opt).from(area).save();
        }
        catch (_) {
            scheduleInteractiveError(getMsg(area, "pdf_unavailable"));
        }
    };
    window.saveAsPDF = saveAsPDF;
    const bindFilterToggle = () => {
        if (!$.fn) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("jQuery unavailable");
            }
            catch (_) {
                console.error(`[pdf] Error:`, _);
            }
            scheduleInteractiveError(getMsg(document.body, "toggle_unavailable"));
            return;
        }
        const btn = document.getElementById("filter"), panel = document.getElementById("show_filter");
        if (!btn || btn.getAttribute(dataFilterGuard) === "true")
            return;
        btn.setAttribute(dataFilterGuard, "true");
        const handler = function () {
            try {
                if (panel) {
                    $("#show_filter").toggle();
                }
                else {
                    scheduleInteractiveError(getMsg(document.body, "toggle_unavailable"));
                }
            }
            catch (_) {
                scheduleInteractiveError(getMsg(document.body, "toggle_unavailable"));
            }
        };
        $(btn).on("click", handler);
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(btn)) {
                $(btn).off("click", handler);
                o.disconnect();
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });
    };
    const ensurePrintHandlers = () => {
        const root = document.documentElement;
        if (root.getAttribute(dataPrintGuard) === "true")
            return;
        root.setAttribute(dataPrintGuard, "true");
        const back = () => {
            try {
                window.close();
            }
            catch (_) {
                console.error(`[pdf] Error:`, _);
            }
            try {
                window.history.back();
            }
            catch (_) {
                console.error(`[pdf] Error:`, _);
            }
        };
        try {
            window.addEventListener("afterprint", back, { once: true });
        }
        catch (_) {
            console.error(`[pdf] Error:`, _);
        }
        const doPrint = () => {
            try {
                window.print();
            }
            catch (_) {
                scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
            }
        };
        if (document.readyState === "complete") {
            doPrint();
        }
        else {
            window.addEventListener("load", doPrint, { once: true });
        }
    };
    const init = () => {
        bindFilterToggle();
        ensurePrintHandlers();
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", init, { once: true })
        : init();
})();
//# sourceMappingURL=pdf.js.map