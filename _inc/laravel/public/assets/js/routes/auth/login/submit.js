(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/auth/login/submit.js
 * @generated from original JavaScript - manual review recommended
 * @module submit
 */



(function () {
    const $ = window.jQuery;
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataErrGuard = "data-error-guard", dataSubmitGuard = "data-submit-guard";
    const qs = (s, r = document) => r.querySelector(s);
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
        const hasBootstrapLink =

        qs('link[rel="stylesheet"][href*="bootstrap"]') ||
            qs('link[href*="bootstrap"]');
        if (hasBootstrapLink && window.bootstrap.Toast) {
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

    };

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

    const bindSubmit = () => {
        const form = qs("#form_data");
        if (!form)
            return;
        if (form.getAttribute(dataSubmitGuard) === "true")

            return;
        form.setAttribute(dataSubmitGuard, "true");

        const handler = function (_e) {
            try {
                const btn = qs("#login_button");
                if (btn) {
                    btn.setAttribute("disabled", "true");
                    return true;
                }
                else {
                    scheduleInteractiveError(getMsg(form, "login_submit_unavailable"));
                    return true;
                }
            }
            catch (_) {
                scheduleInteractiveError(getMsg(form, "login_submit_unavailable"));
                return true;
            }
        };
        $(form).on("submit.loginGuard", handler);
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(form)) {
                $(form).off("submit.loginGuard");
                o.disconnect();
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });
    };
    const init = () => {
        if (!$.fn) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("jQuery unavailable");
            }
            catch (_) {
                console.error(`[submit] Error:`, _);
            }
            scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
            return;
        }
        if (document.readyState === "loading") {
            $(function () {
                bindSubmit();
            });
        }
        else {
            bindSubmit();
        }
    };
    init();
})();
})();