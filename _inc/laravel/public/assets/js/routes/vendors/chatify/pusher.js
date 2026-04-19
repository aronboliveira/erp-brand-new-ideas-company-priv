/**
 * @fileoverview TypeScript version of public/assets/js/routes/vendors/chatify/pusher.js
 * @generated from original JavaScript - manual review recommended
 * @module pusher
 */
(function () {
    const $ = window.jQuery;
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataErrGuard = "data-pusher-error", dataInitGuard = "data-pusher-initialized";
    const qs = (s, r = document) => r.querySelector(s);
    const hasBootstrap = () => !!(
    (qs('link[rel="stylesheet"][href*="bootstrap"]') ??
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
    const getCsrf = () => {
        try {
            const t = $?.('meta[name="csrf-token"]').attr("content");
            return t ? String(t) : "";
        }
        catch (_) {
            const m = document.querySelector('meta[name="csrf-token"]');
            return m?.getAttribute("content") ?? "";
        }
    };
    const initPusher = () => {
        if (document.body.getAttribute(dataInitGuard) === "true")
            return;
        document.body.setAttribute(dataInitGuard, "true");
        try {
            if (!window.Pusher) {
                try {
                    console.error("Pusher library unavailable");
                }
                catch (_) {
                    console.error(`[pusher] Error:`, _);
                }
                schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
                return;
            }
            try {
                window.Pusher.logToConsole = true;
            }
            catch (_) {
                console.error(`[pusher] Error:`, _);
            }
            const key = "{{ config('chatify.pusher.key') }}", cluster = "{{ config('chatify.pusher.options.cluster') }}", authEndpoint = '{{route("pusher.auth")}}';
            if (!key || key === "#" || !cluster || cluster === "#") {
                schedulePointerupError(getMsg(document.body, "pusher_unavailable"));
                return;
            }
            if (!authEndpoint || authEndpoint === "#") {
                schedulePointerupError(getMsg(document.body, "pusher_auth_unavailable"));
                return;
            }
            const headers = { "X-CSRF-TOKEN": getCsrf() };
            const PusherCtor = window.Pusher;
            const pusher = new PusherCtor(key, {
                encrypted: true,
                cluster: cluster,
                authEndpoint: authEndpoint,
                auth: { headers: headers },
            });
            if (!pusher.connection) {
                schedulePointerupError(getMsg(document.body, "pusher_unavailable"));
                return;
            }
            pusher.connection.bind("error", function () {
                schedulePointerupError(getMsg(document.body, "pusher_connect_failed"));
            });
            window.__appPusher = pusher;
        }
        catch (_) {
            schedulePointerupError(getMsg(document.body, "pusher_unavailable"));
        }
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", initPusher, { once: true })
        : initPusher();
})();
//# sourceMappingURL=pusher.js.map