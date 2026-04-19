/**
 * @fileoverview TypeScript version of public/assets/js/routes/installer/dismiss.js
 * @generated from original JavaScript - manual review recommended
 * @module dismiss
 */
(function () {
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataBindGuard = "data-dismiss-bound", dataErrGuard = "data-dismiss-error";
    const qs = (s, r = document) => r.querySelector(s);
    const hasBS = () => !!(
    (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]'))) && !!window.bootstrap.Toast;
    const toastContainer = () => {
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
    const showError = (message) => {
        if (hasBS()) {
            const container = toastContainer();
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
    const scheduleClickError = (msg) => {
        const host = document.body;
        if (!host || host.getAttribute(dataErrGuard) === "true")
            return;
        host.setAttribute(dataErrGuard, "true");
        const once = () => {
            try {
                showError(msg);
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
    const localize = (el, key) => {
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
    const hideAlert = (closeEl) => {
        try {
            const target = qs("#error_alert");
            if (!target) {
                scheduleClickError(localize(closeEl, "dismiss_unavailable"));
                return;
            }
            target.style.display = "none";
        }
        catch (_) {
            scheduleClickError(localize(closeEl, "dismiss_unavailable"));
        }
    };
    const bind = () => {
        const closeEl = qs("#close_alert");
        if (!closeEl)
            return;
        if (closeEl.getAttribute(dataBindGuard) === "true")
            return;
        closeEl.setAttribute(dataBindGuard, "true");
        const onClick = function (e) {
            e.preventDefault();
            hideAlert(this);
        };
        if (!closeEl.getAttribute("data-listener-bound-click")) {
            closeEl.setAttribute("data-listener-bound-click", "1");
            closeEl.addEventListener("click", onClick, { passive: false });
        }
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(closeEl)) {
                closeEl.removeEventListener("click", onClick);
                o.disconnect();
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", bind, { once: true })
        : bind();
})();
//# sourceMappingURL=dismiss.js.map