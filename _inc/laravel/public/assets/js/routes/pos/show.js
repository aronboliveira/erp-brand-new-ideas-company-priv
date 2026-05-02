(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/show.js
 * @generated from original JavaScript - manual review recommended
 * @module show
 */



(function () {
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataGuardListener = "data-guard-listener";

    const msgKey = "pos_unavailable";

    const getMsg = (el) => {
        let msg = errFb;
        try {
            if (!el)
                return msg;
            if (el.getAttribute("data-sv-localized") === "true" ||
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
        }
        catch {
            return errFb;
        }
    };

    const hasBootstrapCss = () => !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');
    const showError = (el) => {
        try {
            const message = getMsg(el);
            if (hasBootstrapCss() && window.bootstrap.Toast) {
                let wrap = document.getElementById("toast-container");
                if (!wrap) {
                    wrap = document.createElement("div");
                    wrap.id = "toast-container";
                    document.body.appendChild(wrap);
                }
                const t = document.createElement("div");
                t.className = "toast";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    t.setAttribute(k, v);
                const b = document.createElement("div");
                b.className = "toast-body";
                b.textContent = message;
                t.appendChild(b);
                wrap.appendChild(t);
                window.bootstrap.Toast.getOrCreateInstance(t, {
                    autohide: true,
                    delay: 4000,
                }).show();
            }
            else {
                alert(message);
            }
        }
        catch {
            alert(errFb);
        }
    };
    try {
        const jq = window.jQuery ?? (window.$?.fn ? window.$ : null);
        if (!jq) {
            if (window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1")
                console.error("jQuery not found for POS guard");
            return;
        }
        jq(() => {
            const bind = (el) => {
                if (!el || el.getAttribute(dataGuardListener) === "true")
                    return;
                el.setAttribute(dataGuardListener, "true");
                const $el = jq(el);
                const handler = (e) => {
                    try {
                        const url = el.getAttribute("data-url");
                        const href = el.getAttribute("href") ||
                            (el.form
                                ? el.form.getAttribute("action")
                                : null);
                        if ((!url || url === "#") && (!href || href === "#")) {
                            e.preventDefault();
                            showError(el);
                        }
                    }
                    catch {
                        e.preventDefault();
                        showError(el);
                    }
                };
                $el.on("click.posGuard", handler);
                const obs = new MutationObserver(() => {
                    if (!document.body.contains(el)) {
                        try {
                            $el.off("click.posGuard", handler);
                        }
                        catch (__err) {
                            console.error(`[show] Error:`, __err);
                        }
                        obs.disconnect();
                    }
                });
                obs.observe(document.body, { childList: true, subtree: true });
            };
            try {
                const nodes = document.querySelectorAll(".payment-done-btn");
                nodes.forEach(n => bind(n));
            }
            catch {
                jq(".payment-done-btn").toArray().forEach(bind);
            }
        });
    }
    catch {
        try {
            alert(errFb);
        }
        catch (__err) {
            console.error(`[show] Error:`, __err);
        }
    }
})();
})();