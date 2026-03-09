(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/trials/balance/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars
(function () {
    const $ = window.jQuery;
    function qs(s, r = document) {
        return r.querySelector(s);
    }
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const qsa = (s, r = document) => Array.from(r.querySelectorAll(s)), errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataErrGuard = "data-error-guard", dataListenerGuard = "data-listener-guard";
    const getMsg = (el, key) => {
        let msg = errFb;
        if (el?.getAttribute(dataSvLocalized) === "true" ||
            el?.getAttribute(dataClientLocalized) === "true") {
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
                    el?.getAttribute(dataGuardMsg) ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
            if (el && msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        return msg;
    };
    if (!$) {
        try {
            if (window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1")
                console.error("jQuery unavailable");
        }
        catch (_) {
            console.error(`[pdf] Error:`, _);
        }
        scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
        return;
    }
    const ensureToastContainer = () => {
        const id = "np-toast-container", c = qs("#" + id);
        if (c)
            return c;
        const div = document.createElement("div");
        div.id = id;
        div.setAttribute("aria-live", "polite");
        div.setAttribute("aria-atomic", "true");
        Object.assign(div.style, { position: "fixed", top: "1rem", right: "1rem" });
        document.body.appendChild(div);
        return div;
    };
    const showErrorNow = (message) => {
        const hasBootstrap = (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
            qs('link[href*="bootstrap"]')) &&
            window.bootstrap.Toast;
        if (hasBootstrap) {
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
    function scheduleInteractiveError(message) {
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
    }
    const bindWithObserver = (el, evt, handler, flag) => {
        if (!el || el.getAttribute(flag) === "true")
            return;
        el.setAttribute(flag, "true");
        $(el).on(evt, handler);
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(el)) {
                $(el).off(evt, handler);
                o.disconnect();
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });
    };
    const printAreaSafely = () => {
        const src = qs("#printableArea");
        if (!src) {
            scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
            return;
        }
        let iframe = qs("#np-print-iframe");
        if (!iframe) {
            const newIframe = document.createElement("iframe");
            newIframe.id = "np-print-iframe";
            Object.assign(newIframe.style, {
                position: "fixed",
                right: "0",
                bottom: "0",
                width: "0",
                height: "0",
                border: "0",
            });
            document.body.appendChild(newIframe);
            iframe = newIframe;
        }
        const doc = iframe.contentWindow?.document;
        if (!doc) {
            scheduleInteractiveError(getMsg(src, "print_unavailable"));
            return;
        }
        const cssNodes = qsa('link[rel="stylesheet"], style', document.head ?? document).map(n => n.cloneNode(true));
        doc.open();
        doc.write("<!doctype html><html><head></head><body></body></html>");
        doc.close();
        cssNodes.forEach(n => doc.head.appendChild(n));
        const wrapper = doc.createElement("div");
        wrapper.innerHTML = src.innerHTML;
        doc.body.appendChild(wrapper);
        const done = () => {
            try {
                iframe.contentWindow?.focus();
                iframe.contentWindow?.print();
            }
            catch (_) {
                scheduleInteractiveError(getMsg(src, "print_unavailable"));
            }
        };
        if (doc.readyState === "complete") {
            setTimeout(done, 0);
        }
        else {
            doc.addEventListener("readystatechange", function onr() {
                if (doc.readyState === "complete") {
                    doc.removeEventListener("readystatechange", onr);
                    done();
                }
            });
        }
    };
    window.saveAsPDF = printAreaSafely;
    const onFilterClick = () => {
        $("#show_filter").toggle();
    };
    const init = () => {
        const filterBtn = document.getElementById("filter");
        if (!filterBtn)
            return;
        bindWithObserver(filterBtn, "click", onFilterClick, dataListenerGuard + "-filter");
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", init, { once: true })
        : init();
})();
})();