/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/profits/horizontal/loss/toggle.js
 * @generated from original JavaScript - manual review recommended
 * @module toggle
 */
(function () {
    const $ = window.jQuery;
    const qs = (s, r = document) => r.querySelector(s), errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg";
    const dataFilterGuard = "data-filter-guard";
    const getMsg = (el, key) => {
        let msg = errFb;
        if (el.getAttribute("data-sv-localized") === "true" ||
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
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        return msg;
    };
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
    const scheduleInteractiveError = (message) => {
        const host = document.body;
        if (!host || host.getAttribute("data-error-guard") === "true")
            return;
        host.setAttribute("data-error-guard", "true");
        const once = () => {
            try {
                showErrorNow(message);
            }
            finally {
                host.removeAttribute("data-error-guard");
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
    if (!$?.fn) {
        try {
            if (window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1")
                console.error("jQuery unavailable");
        }
        catch (_) {
            console.error(`[toggle] Error:`, _);
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
                showErrorNow(getMsg(document.body, "toggle_unavailable"));
            }
        }
        catch (_) {
            showErrorNow(getMsg(document.body, "toggle_unavailable"));
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
})();
//# sourceMappingURL=toggle.js.map