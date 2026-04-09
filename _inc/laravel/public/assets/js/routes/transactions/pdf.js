/**
 * @fileoverview TypeScript version of public/assets/js/routes/transactions/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
    const $ = window.jQuery;
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataErrGuard = "data-pdf-error";
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const qs = (s, r = document) => r.querySelector(s);
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const hasBootstrap = () => !!(
    // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
    (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]'))) && !!window.bootstrap.Toast;
    const ensureToastContainer = () => {
        const existing = qs("#np-toast-container");
        if (existing)
            return existing;
        const c = document.createElement("div");
        c.id = "np-toast-container";
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        Object.assign(c.style, { position: "fixed", top: "1rem", right: "1rem" });
        document.body.appendChild(c);
        return c;
    };
    const showErrorNow = (message) => {
        if (hasBootstrap()) {
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
                new window.bootstrap.Toast(t, {
                    autohide: true,
                    delay: 4000,
                }).show();
            }
            catch (_) {
                alert(message ?? errFb);
            }
        }
        else {
            alert(message ?? errFb);
        }
    };
    const schedulePointerupError = (message) => {
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
        document.addEventListener("pointerup", once, { once: true });
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(host)) {
                document.removeEventListener("pointerup", once);
                o.disconnect();
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
        // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    };
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const getMsg = (el, key) => {
        let msg = errFb;
        if (el.getAttribute(dataSvLocalized) === "true" ||
            el.getAttribute(dataClientLocalized) === "true") {
            msg = el.getAttribute(dataGuardMsg) || errFb;
        }
        else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") ??
                document.documentElement.lang ??
                "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            const msgKey = key;
            msg =
                window.translations?.[lang]?.[msgKey] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
            if (msg !== errFb && el) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        return msg;
    };
    const filenameFrom = () => {
        try {
            if (!$)
                return "download";
            return String($("#filename").val() ?? "").trim() || "download";
        }
        catch (_) {
            return "download";
        }
    };
    const ensureHtml2Pdf = () => {
        if (typeof window.html2pdf === "function")
            return true;
        try {
            if (window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1")
                console.error("html2pdf unavailable");
        }
        catch (_) {
            console.error(`[pdf] Error:`, _);
        }
        schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
        return false;
    };
    const doSave = (el) => {
        if (!ensureHtml2Pdf())
            return;
        const area = document.getElementById("printableArea");
        if (!area) {
            schedulePointerupError(getMsg(document.body, "pdf_unavailable"));
            return;
        }
        const opt = {
            margin: 0.3,
            filename: filenameFrom(),
            image: { type: "jpeg", quality: 1 },
            html2canvas: { scale: 4, dpi: 72, letterRendering: true },
            jsPDF: { unit: "in", format: "A4" },
        };
        try {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-call
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-explicit-any, @typescript-eslint/no-unsafe-call
            window.html2pdf().set(opt).from(area).save();
        }
        catch (_) {
            schedulePointerupError(getMsg(el ?? document.body, "pdf_unavailable"));
        }
    };
    if (!window.saveAsPDF)
        window.saveAsPDF = function () {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
            doSave(document.body);
        };
})();
//# sourceMappingURL=pdf.js.map