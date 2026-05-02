(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/users/profiles/scroll.js
 * @generated from original JavaScript - manual review recommended
 * @module scroll
 */



(function () {
    const $ = window.jQuery;
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataBindGuard = "data-scrollspy-bound", dataClickGuard = "data-listgroup-click-bound", dataErrGuard = "data-scrollspy-error";

    const qs = (s, r = document) => r.querySelector(s);

    const hasBS = () => !!(

    (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]'))) && !!window.bootstrap.Toast;
    const ensureToast = () => {
        let c = qs("#np-toast-container");
        if (c)
            return c;
        c = document.createElement("div");
        c.id = "np-toast-container";
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        Object.assign(c.style, { position: "fixed", top: "1rem", right: "1rem" });
        document.body.appendChild(c);
        return c;
    };
    const showErrorNow = (msg) => {
        if (hasBS()) {
            const container = ensureToast();
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
            const body = t.querySelector(".toast-body");
            if (body)
                body.textContent = msg ?? errFb;
            try {
                new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
            }
            catch (_) {
                alert(msg ?? errFb);
            }
        }
        else {
            alert(msg ?? errFb);
        }
    };
    const scheduleClickError = (msg) => {
        const host = document.body;
        if (!host || host.getAttribute(dataErrGuard) === "true")
            return;
        host.setAttribute(dataErrGuard, "true");
        const once = () => {
            try {
                showErrorNow(msg);
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
            el.getAttribute(dataClientLocalized) === "true")
            msg = el.getAttribute(dataGuardMsg) || errFb;
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
    const initScrollSpy = () => {
        const target = qs("#useradd-sidenav") ?? document.body;
        if (target.getAttribute(dataBindGuard) === "true")
            return;
        target.setAttribute(dataBindGuard, "true");
        try {
            if (!window.bootstrap.ScrollSpy) {
                try {
                    if (window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1")
                        console.error("bootstrap.ScrollSpy unavailable");
                }
                catch (_) {
                    console.error(`[scroll] Error:`, _);
                }
                scheduleClickError(getMsg(target, "scrollspy_unavailable"));
                return;
            }
            new window.bootstrap.ScrollSpy(document.body, {
                target: "#useradd-sidenav",
                offset: 300,
            });
        }
        catch (_) {
            scheduleClickError(getMsg(target, "scrollspy_unavailable"));
        }
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(target))
                o.disconnect();
        });

        mo.observe(document.body, { childList: true, subtree: true });
    };

    const bindListClicks = () => {
        const root = document.body;
        if (root.getAttribute(dataClickGuard) === "true")
            return;
        root.setAttribute(dataClickGuard, "true");
        if (!$?.fn) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("jQuery unavailable");
            }
            catch (_) {
                console.error(`[scroll] Error:`, _);
            }
            return;
        }
        const ns = ".lgitem";
        $(document).on("click" + ns, ".list-group-item", function () {
            try {



                const href = this.getAttribute("href") ?? "";
                const $all = $(".list-group-item");
                if ($all.length)
                    $all
                        .filter(function () {
                        return (this.getAttribute("href") ?? "") === href;
                    })
                        .parent()
                        .removeClass("text-primary");
            }
            catch (_) {
                console.error(`[scroll] Error:`, _);
            }
        });
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(root)) {
                $(document).off(ns);
                o.disconnect();
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });
    };
    const init = () => {
        initScrollSpy();
        bindListClicks();
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", init, { once: true })
        : init();
})();
})();