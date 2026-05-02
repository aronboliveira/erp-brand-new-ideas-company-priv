(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/timeTrackers/confirm.js
 * @generated from original JavaScript - manual review recommended
 * @module confirm
 */



(function () {
    const $ = window.jQuery;
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataBound = "data-confirm-bound", dataErrGuard = "data-error-guard", dataFallbackBound = "data-confirm-fallback";
    const qs = (s, r = document) => r.querySelector(s);

    const hasBootstrap = () => qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        (qs('link[href*="bootstrap"]') && window.bootstrap.Toast);
    const ensureToastContainer = () => {
        const c = qs("#np-toast-container");
        if (c)
            return c;
        const el = document.createElement("div");
        el.id = "np-toast-container";
        el.setAttribute("aria-live", "polite");
        el.setAttribute("aria-atomic", "true");
        Object.assign(el.style, { position: "fixed", top: "1rem", right: "1rem" });
        document.body.appendChild(el);
        return el;
    };
    const showErrorNow = (message) => {
        if (hasBootstrap()) {
            const container = ensureToastContainer();
            let t = qs("#np-toast", container);
            if (!t) {
                const toastEl = document.createElement("div");
                toastEl.id = "np-toast";
                toastEl.className = "toast";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    toastEl.setAttribute(k, v);
                toastEl.innerHTML =
                    '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                container.appendChild(toastEl);
                t = toastEl;
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
    const scheduleInteractiveError = (target, message) => {
        if (!target || target.getAttribute(dataErrGuard) === "true")
            return;
        target.setAttribute(dataErrGuard, "true");
        const once = () => {
            try {
                showErrorNow(message);
            }
            finally {
                target.removeAttribute(dataErrGuard);
            }
        };
        if (!target.getAttribute("data-listener-bound-click")) {
            target.setAttribute("data-listener-bound-click", "1");
            target.addEventListener("click", once, { once: true });
        }
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(target)) {
                target.removeEventListener("click", once);
                o.disconnect();
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });

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
    const bindConfirmModals = () => {
        if (!$.fn) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("jQuery unavailable");
            }
            catch (_) {
                console.error(`[confirm] Error:`, _);
            }
            document
                .querySelectorAll("[data-confirm-delete]")
                .forEach((el) => {
                scheduleInteractiveError(el, getMsg(el, "plugin_unavailable"));
            });
            return;
        }
        const hasFireModal = typeof $.fn.fireModal ===
            "function";
        document
            .querySelectorAll("[data-confirm-delete]")
            .forEach((el) => {
            const htmlEl = el, me = $(htmlEl);
            if (htmlEl.getAttribute(dataBound) === "true")
                return;
            htmlEl.setAttribute(dataBound, "true");
            let meData = me.data("confirm-delete");
            meData = meData == null ? "" : String(meData);
            const parts = meData.split("|"), title = parts[0] ?? "", body = parts.slice(1).join("|") ?? "";
            if (hasFireModal) {
                try {
                    me.fireModal?.({
                        title: title,
                        body: body,
                        buttons: [
                            {
                                text: String(me.data("confirm-text-yes") ?? "Yes"),
                                class: "btn btn-sm btn-danger rounded-pill",
                                handler: function (modal) {
                                    try {
                                        const yesCode = String(me.data("confirm-yes") ?? "");
                                        if (yesCode) {
                                            // SECURITY: Replace eval with safe handler dispatch
                                            const confirmHandler = window.__confirmHandlers?.[yesCode];
                                            if (typeof confirmHandler === "function") {
                                                confirmHandler();
                                            }
                                            else {
                                                safeFormAction(yesCode, me.get(0));
                                            }
                                        }
                                    }
                                    catch (_) {
                                        console.error(`[confirm] Error:`, _);
                                    }
                                    try {
                                        const destroyFn = $.destroyModal;
                                        if (destroyFn) {
                                            destroyFn(modal);
                                        }
                                        else {
                                            modal.remove();
                                        }
                                    }
                                    catch (_) {
                                        console.error(`[confirm] Error:`, _);
                                    }
                                },
                            },
                            {
                                text: String(me.data("confirm-text-cancel") ?? "Cancel"),
                                class: "btn btn-sm btn-secondary rounded-pill",
                                handler: function (modal) {
                                    try {
                                        const destroyFn = $.destroyModal;
                                        if (destroyFn) {
                                            destroyFn(modal);
                                        }
                                        else {
                                            modal.remove();
                                        }
                                    }
                                    catch (_) {
                                        console.error(`[confirm] Error:`, _);
                                    }
                                    try {
                                        const noCode = String(me.data("confirm-no") ?? "");
                                        if (noCode) {
                                            // SECURITY: Replace eval with safe handler dispatch
                                            const confirmHandler = window.__confirmHandlers?.[noCode];
                                            if (typeof confirmHandler === "function") {
                                                confirmHandler();
                                            }
                                            else {
                                                safeFormAction(noCode, me.get(0));
                                            }
                                        }
                                    }
                                    catch (_) {
                                        console.error(`[confirm] Error:`, _);
                                    }
                                },
                            },
                        ],
                    });
                }
                catch (_) {
                    scheduleInteractiveError(htmlEl, getMsg(htmlEl, "confirm_unavailable"));
                }
            }
            else {
                if (htmlEl.getAttribute(dataFallbackBound) !== "true") {
                    htmlEl.setAttribute(dataFallbackBound, "true");
                    const handler = function () {
                        showErrorNow(getMsg(htmlEl, "confirm_unavailable"));
                    };
                    $(htmlEl).on("click.confirmFallback", handler);
                    const mo = new MutationObserver((_m, o) => {
                        if (!document.body.contains(htmlEl)) {
                            $(htmlEl).off("click.confirmFallback", handler);
                            o.disconnect();
                        }
                    });
                    mo.observe(document.body, { childList: true, subtree: true });
                }
            }
        });
    };
    // SECURITY: Safe fallback for confirm handlers instead of eval()
    const safeFormAction = (actionStr, _element) => {
        if (!actionStr)
            return;
        // If it looks like a form selector, submit that form
        if (actionStr.startsWith("#") || actionStr.startsWith(".")) {
            const form = qs(actionStr);
            if (form?.tagName === "FORM")
                form.submit();
            return;
        }
        // If it starts with a safe URL protocol, navigate to it
        if (/^(https?:\/\/|\/)/.test(actionStr) &&
            !/^javascript:/i.test(actionStr)) {
            window.location.href = actionStr;
            return;
        }
    };
    const init = () => {
        bindConfirmModals();
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", init, { once: true })
        : init();
})();
})();