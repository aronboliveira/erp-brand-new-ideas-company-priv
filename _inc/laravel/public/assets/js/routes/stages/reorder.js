(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/stages/reorder.js
 * @generated from original JavaScript - manual review recommended
 * @module reorder
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
    const $ = window.jQuery;
    const qs = (s, r = document) => r.querySelector(s);
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataErrGuard = "data-error-guard", dataSortGuard = "data-sort-guard";
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
        document.addEventListener("pointerup", once, { once: true });
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(host)) {
                document.removeEventListener("pointerup", once);
                o.disconnect();
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
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
            if (el && msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        return msg;
    };
    const initSortable = () => {
        if (!$.fn) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("jQuery unavailable");
            }
            catch (_) {
                console.error(`[reorder] Error:`, _);
            }
            scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
            return;
        }
        if (!$.fn.sortable) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("jQuery UI sortable unavailable");
            }
            catch (_) {
                console.error(`[reorder] Error:`, _);
            }
            scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
            return;
        }
        const $lists = $(".sortable");
        if (!$lists.length)
            return;
        $lists.each(function () {
            const el = this;
            if (el.getAttribute(dataSortGuard) === "true")
                return;
            el.setAttribute(dataSortGuard, "true");
            try {
                const $el = $(el);
                if (typeof $el.disableSelection === "function")
                    $el.disableSelection();
                $el.sortable({
                    stop: function () {
                        try {
                            const order = [];
                            $(this)
                                .find("li")
                                .each(function (i) {
                                order[i] = String($(this).attr("data-id") ?? $(this).data("id") ?? "");
                            });
                            const urlAttr = el.getAttribute("data-url") ?? "", href = el.tagName === "FORM"
                                ? (el.getAttribute("action") ?? "")
                                : (el.getAttribute("href") ?? "");
                            if ((!urlAttr || urlAttr === "#") && (!href || href === "#")) {
                                scheduleInteractiveError(getMsg(el, "reorder_unavailable"));
                                return;
                            }
                            const endpoint = urlAttr && urlAttr !== "#" ? urlAttr : href, token = String($('meta[name="csrf-token"]').attr("content") ?? "");
                            $.ajax({
                                url: endpoint,
                                type: "POST",
                                data: { order },
                                headers: token ? { "X-CSRF-TOKEN": token } : undefined,
                                cache: false,
                                success: function () { },
                                error: function () {
                                    scheduleInteractiveError(getMsg(el, "ajax_unavailable"));
                                },
                            });
                        }
                        catch (_) {
                            scheduleInteractiveError(getMsg(el, "reorder_unavailable"));
                        }
                    },
                });
                const mo = new MutationObserver((_m, o) => {
                    if (!document.body.contains(el)) {
                        try {
                            $(el).sortable("destroy");
                        }
                        catch (_) {
                            console.error(`[reorder] Error:`, _);
                        }
                        o.disconnect();
                    }
                });
                mo.observe(document.body, { childList: true, subtree: true });
            }
            catch (_) {
                scheduleInteractiveError(getMsg(el, "plugin_unavailable"));
            }
        });
    };
    const init = () => {
        initSortable();
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", init, { once: true })
        : init();
})();
})();