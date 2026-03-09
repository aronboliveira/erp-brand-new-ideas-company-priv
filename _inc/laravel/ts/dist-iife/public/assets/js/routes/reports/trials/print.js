(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/trials/print.js
 * @generated from original JavaScript - manual review recommended
 * @module print
 */
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
    const qs = (s, r = document) => r.querySelector(s);
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataErrGuard = "data-error-guard", dataPrintBound = "data-print-init-bound";
    const ensureToastContainer = () => {
        const id = "np-toast-container";
        let c = qs("#" + id);
        if (c)
            return c;
        c = document.createElement("div");
        c.id = id;
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        Object.assign(c.style, { position: "fixed", top: "1rem", right: "1rem" });
        document.body.appendChild(c);
        return c;
    };
    const showErrorNow = (message) => {
        const hasBsLink = 
        // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
        qs('link[rel="stylesheet"][href*="bootstrap"]') ||
            qs('link[href*="bootstrap"]');
        const hasBsToast = window.bootstrap.Toast;
        if (hasBsLink && hasBsToast) {
            const container = ensureToastContainer(), tid = "np-toast";
            let t = qs("#" + tid, container);
            if (!t) {
                t = document.createElement("div");
                t.id = tid;
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
    const scheduleInteractiveError = (message) => {
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
        document.addEventListener("click", once, { once: true });
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(host)) {
                document.removeEventListener("click", once);
                o.disconnect();
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
        // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    };
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const getMsg = (el, msgKey) => {
        const errFbL = errFb, dataClientLocalizedL = dataClientLocalized, dataGuardMsgL = dataGuardMsg;
        let msg = errFbL;
        if (el.getAttribute("data-sv-localized") === "true" ||
            el.getAttribute(dataClientLocalizedL) === "true") {
            msg = el.getAttribute(dataGuardMsgL) || errFbL;
        }
        else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") ??
                document.documentElement.lang ??
                "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[msgKey] ||
                    el.getAttribute(dataGuardMsgL) ||
                    window.translations?.en?.[msgKey] ||
                    errFbL;
            if (msg !== errFbL) {
                el.setAttribute(dataGuardMsgL, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        return msg;
    };
    const goBack = () => {
        try {
            window.close();
        }
        catch (_) {
            console.error(`[print] Error:`, _);
        }
        try {
            if (window.history && typeof window.history.back === "function")
                window.history.back();
        }
        catch (_) {
            console.error(`[print] Error:`, _);
        }
    };
    const init = () => {
        const root = document.documentElement;
        if (root.getAttribute(dataPrintBound) === "true")
            return;
        root.setAttribute(dataPrintBound, "true");
        let printed = false;
        const onAfterPrint = () => {
            printed = true;
            goBack();
        };
        if ("onafterprint" in window) {
            window.onafterprint = onAfterPrint;
        }
        else {
            window.addEventListener("afterprint", onAfterPrint);
        }
        try {
            if (typeof window.print === "function") {
                window.print();
            }
            else {
                scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
            }
        }
        catch (_) {
            scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
        }
        setTimeout(function () {
            if (!printed)
                scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
        }, 2000);
        const mo = new MutationObserver((_m, o) => {
            if (!document.documentElement.isConnected) {
                window.removeEventListener("afterprint", onAfterPrint);
                o.disconnect();
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", init, { once: true })
        : init();
})();
})();