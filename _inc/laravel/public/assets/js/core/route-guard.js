/**
 * route-guard.ts — Legacy route guard implementation (backward compat).
 *
 * @deprecated Use erp-guard.ts / erp-guard.js instead.
 *
 * If ERPGuard exists, creates a compatibility shim mapping
 * `window.RouteGuard` → `window.ERPGuard`. Otherwise provides
 * a standalone fallback implementation.
 *
 * Mirror of public/assets/js/core/route-guard.js
 * @module core/route-guard
 */
/* ---------- Logging ----------------------------------------------------- */
const _emitted = {};
const isDev = () => location.hostname === "localhost" || location.hostname === "127.0.0.1";
const devError = (ctx, err) => {
    if (!isDev())
        return;
    const msg = err instanceof Error ? err.message : String(err);
    const key = `${ctx}:${msg}`;
    if (_emitted[key])
        return;
    _emitted[key] = true;
    console.error(`[RouteGuard:${ctx}]`, msg);
};
/* ---------- Implementation ---------------------------------------------- */
(function () {
    "use strict";
    // Referência local tipada para evitar encadeamento repetido
    const guard = window.ERPGuard;
    /* ── Compatibility layer when ERPGuard is present ─────────────── */
    if (guard) {
        window.RouteGuard = {
            init: () => void 0,
            showToast: (msg, type) => {
                // Shim de compatibilidade — tipo string da API legada mapeado para ToastType
                guard.showToast(msg, type);
            },
            scheduleInteractiveError: (msg) => guard.scheduleInteractiveError(msg),
            getMsg: (_el, key) => guard.getMsg(key),
            attachGuard: (el) => guard.bindClickGuard(el),
            guardById: (id) => guard.bindClickGuard(document.getElementById(id)),
            guardMultiple: (...ids) => ids.flat().forEach((id) => guard.bindClickGuard(document.getElementById(id))),
            guardFormSubmit: (formId, opts) => guard.bindSubmitGuard(typeof formId === "string"
                ? document.querySelector("#" + formId)
                : formId, null, opts),
            guardAllInContainer: (containerId, selector) => {
                const sel = selector || '[data-route-guard], [data-sv-localized="true"]';
                const container = document.getElementById(containerId);
                if (container) {
                    container
                        .querySelectorAll(sel)
                        .forEach((el) => guard.bindClickGuard(el));
                }
            },
            guardOnChange: (elId, callback) => {
                const el = typeof elId === "string" ? document.getElementById(elId) : elId;
                if (el && !el.hasAttribute("data-change-listener")) {
                    el.setAttribute("data-change-listener", "true");
                    el.addEventListener("change", (e) => callback?.(e, el));
                }
            },
            csrfToken: () => guard.getCsrfToken(),
            ajaxPost: (url, data, opts) => guard.ajaxPost(url, data, opts),
            isInvalidUrl: (url) => guard.isInvalidUrl(url ?? ""),
            animations: {
                fadeIn: () => Promise.resolve(),
                fadeOut: () => Promise.resolve(),
                slideDown: () => Promise.resolve(),
                slideUp: () => Promise.resolve(),
                addAnimation: () => Promise.resolve(),
            },
            logError: (context, err) => devError(context, err),
            safeFetch: (url, opts) => guard.safeFetch(url, opts),
            TOAST_CONTAINER_ID: "erp-toast-container",
            DATA_GUARD_MSG: "data-guard-msg",
            DATA_SV_LOCALIZED: "data-sv-localized",
            DATA_LISTENER_ACTIVE: "data-listener-active",
            DATA_FAILED_ROUTE: "data-failed-route",
        };
        return;
    }
    /* ── Standalone fallback ─────────────────────────────────────── */
    const DATA_GUARD_MSG = "data-guard-msg", DATA_SV_LOCALIZED = "data-sv-localized", DATA_CLIENT_LOCALIZED = "data-client-localized", DATA_LISTENER_ACTIVE = "data-listener-active", DATA_FAILED_ROUTE = "data-failed-route", DATA_ERROR_GUARD = "data-error-guard", TOAST_CONTAINER_ID = "np-toast-container", ERR_FALLBACK = "# ERROR";
    const qs = (sel, root) => (root ?? document).querySelector(sel);
    const ensureToastContainer = () => {
        let c = qs("#" + TOAST_CONTAINER_ID);
        if (c)
            return c;
        c = document.createElement("div");
        c.id = TOAST_CONTAINER_ID;
        c.className = "toast-container position-fixed top-0 end-0 p-3";
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        c.style.zIndex = "1100";
        document.body.appendChild(c);
        return c;
    };
    const hasBootstrap = () => !!(qs('link[href*="bootstrap"]') && window.bootstrap?.Toast);
    const showToast = (message, type) => {
        const msg = message || ERR_FALLBACK, toastType = type || "error";
        if (hasBootstrap()) {
            const container = ensureToastContainer(), id = "np-toast-" + Date.now(), toast = document.createElement("div");
            toast.id = id;
            toast.className = "toast fade";
            toast.setAttribute("role", "alert");
            toast.setAttribute("aria-live", "assertive");
            toast.setAttribute("aria-atomic", "true");
            const bgClass = toastType === "success"
                ? "bg-success"
                : toastType === "warning"
                    ? "bg-warning"
                    : "bg-danger";
            toast.innerHTML = `<div class="toast-header ${bgClass} text-white"><strong class="me-auto">${toastType === "success" ? "Success" : "Notice"}</strong><button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">${msg}</div>`;
            container.appendChild(toast);
            const bsToast = new (window.bootstrap.Toast)(toast, {
                autohide: true,
                delay: 4000,
            });
            bsToast.show();
            toast.addEventListener("hidden.bs.toast", () => toast.remove());
        }
        else {
            alert(msg);
        }
    };
    const scheduleInteractiveError = (message) => {
        const host = document.body;
        if (!host || host.getAttribute(DATA_ERROR_GUARD) === "true")
            return;
        host.setAttribute(DATA_ERROR_GUARD, "true");
        const once = () => {
            try {
                showToast(message, "error");
            }
            finally {
                host.removeAttribute(DATA_ERROR_GUARD);
            }
        };
        document.addEventListener("pointerup", once, { once: true });
        const mo = new MutationObserver((_, o) => {
            if (!document.body.contains(host)) {
                document.removeEventListener("pointerup", once);
                o.disconnect();
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    const getMsg = (el, fallbackKey) => {
        if (el?.getAttribute?.(DATA_SV_LOCALIZED) === "true" ||
            el?.getAttribute?.(DATA_CLIENT_LOCALIZED) === "true")
            return el.getAttribute(DATA_GUARD_MSG) || ERR_FALLBACK;
        let lang = (window.sessionStorage.getItem("erp-np-lang") ||
            document.documentElement.lang ||
            "en")
            .toLowerCase()
            .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        const msg = window.translations?.[lang]?.[fallbackKey] ||
            el?.getAttribute?.(DATA_GUARD_MSG) ||
            window.translations?.en?.[fallbackKey] ||
            ERR_FALLBACK;
        if (el && msg !== ERR_FALLBACK) {
            el.setAttribute(DATA_GUARD_MSG, msg);
            el.setAttribute(DATA_CLIENT_LOCALIZED, "true");
        }
        return msg;
    };
    const isInvalidUrl = (url) => !url || url === "#";
    const attachRouteGuard = (el) => {
        if (!el || el.getAttribute(DATA_LISTENER_ACTIVE) === "true")
            return;
        el.setAttribute(DATA_LISTENER_ACTIVE, "true");
        const evtType = el.tagName === "FORM"
            ? "submit"
            : el.type === "checkbox" ||
                el.type === "range"
                ? "change"
                : "click";
        el.addEventListener(evtType, (e) => {
            const isForm = el.tagName === "FORM", href = isForm ? null : el.getAttribute("href") || "", action = el.getAttribute("action") || "", url = el.getAttribute("data-url") || href || action || "#";
            if (isForm) {
                if (!isInvalidUrl(url) && !isInvalidUrl(action))
                    return;
            }
            else {
                if (!isInvalidUrl(url) && !isInvalidUrl(href))
                    return;
            }
            e.preventDefault();
            if (el.type === "checkbox")
                el.checked = !el.checked;
            if (el.type === "range" &&
                el.dataset.startVal)
                el.value =
                    el.dataset.startVal;
            showToast(getMsg(el, "route_unavailable"), "error");
            el.setAttribute(DATA_FAILED_ROUTE, "true");
        });
    };
    const initRouteGuards = () => {
        document
            .querySelectorAll('[data-route-guard], [data-sv-localized="true"]')
            .forEach((n) => attachRouteGuard(n));
        const mo = new MutationObserver((muts) => {
            for (const m of muts) {
                m.addedNodes.forEach((n) => {
                    if (n.nodeType === 1) {
                        const el = n;
                        if (el.matches?.('[data-route-guard], [data-sv-localized="true"]'))
                            attachRouteGuard(el);
                        el
                            .querySelectorAll?.('[data-route-guard], [data-sv-localized="true"]')
                            .forEach((c) => attachRouteGuard(c));
                    }
                });
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });
    };
    const addAnimation = (el, animClass, duration) => {
        if (!el)
            return Promise.resolve();
        el.classList.add(animClass);
        return new Promise((r) => setTimeout(() => {
            el.classList.remove(animClass);
            r();
        }, duration || 300));
    };
    const fadeIn = (el, dur) => {
        if (!el)
            return Promise.resolve();
        el.style.opacity = "0";
        el.style.display = "";
        el.style.transition = `opacity ${dur || 300}ms ease`;
        requestAnimationFrame(() => (el.style.opacity = "1"));
        return new Promise((r) => setTimeout(r, dur || 300));
    };
    const fadeOut = (el, dur) => {
        if (!el)
            return Promise.resolve();
        el.style.transition = `opacity ${dur || 300}ms ease`;
        el.style.opacity = "0";
        return new Promise((r) => setTimeout(() => {
            el.style.display = "none";
            r();
        }, dur || 300));
    };
    const slideDown = (el, dur) => {
        if (!el)
            return Promise.resolve();
        el.style.height = "0";
        el.style.overflow = "hidden";
        el.style.display = "";
        const h = el.scrollHeight;
        el.style.transition = `height ${dur || 300}ms ease`;
        requestAnimationFrame(() => (el.style.height = h + "px"));
        return new Promise((r) => setTimeout(() => {
            el.style.height = "";
            el.style.overflow = "";
            r();
        }, dur || 300));
    };
    const slideUp = (el, dur) => {
        if (!el)
            return Promise.resolve();
        el.style.height = el.scrollHeight + "px";
        el.style.overflow = "hidden";
        el.style.transition = `height ${dur || 300}ms ease`;
        requestAnimationFrame(() => (el.style.height = "0"));
        return new Promise((r) => setTimeout(() => {
            el.style.display = "none";
            el.style.height = "";
            el.style.overflow = "";
            r();
        }, dur || 300));
    };
    const logError = (context, err) => {
        devError(context, err);
    };
    const safeFetch = async (url, opts) => {
        if (isInvalidUrl(url))
            return { ok: false, error: "Invalid URL" };
        try {
            const resp = await fetch(url, {
                ...opts,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                    ...(opts?.headers ?? {}),
                },
            });
            if (!resp.ok)
                return { ok: false, status: resp.status, error: resp.statusText };
            const data = await resp.json().catch(() => ({}));
            return { ok: true, data };
        }
        catch (e) {
            logError("safeFetch", e);
            return { ok: false, error: e instanceof Error ? e.message : String(e) };
        }
    };
    const guardById = (id) => {
        if (!id)
            return;
        const el = document.getElementById(id);
        if (el)
            attachRouteGuard(el);
    };
    const guardMultiple = (...ids) => {
        ids.flat().forEach(guardById);
    };
    const guardFormSubmit = (formId, opts) => {
        const form = typeof formId === "string"
            ? document.getElementById(formId)
            : formId;
        if (!form || form.getAttribute(DATA_LISTENER_ACTIVE) === "true")
            return;
        form.setAttribute(DATA_LISTENER_ACTIVE, "true");
        form.addEventListener("submit", (e) => {
            const action = form.getAttribute("action") || "", url = form.getAttribute("data-url") || action || "#";
            if (!isInvalidUrl(url) && !isInvalidUrl(action))
                return;
            e.preventDefault();
            showToast(getMsg(form, opts?.msgKey || "form_submit_route_unavailable"), "error");
            form.setAttribute(DATA_FAILED_ROUTE, "true");
        });
    };
    const guardAllInContainer = (containerId, selector) => {
        const container = document.getElementById(containerId);
        if (!container)
            return;
        const sel = selector ||
            '[data-route-guard], [data-sv-localized="true"], [data-guard-msg]';
        container
            .querySelectorAll(sel)
            .forEach((n) => attachRouteGuard(n));
    };
    const guardOnChange = (elId, callback) => {
        const el = typeof elId === "string" ? document.getElementById(elId) : elId;
        if (!el || el.getAttribute("data-change-listener") === "true")
            return;
        el.setAttribute("data-change-listener", "true");
        el.addEventListener("change", (e) => {
            try {
                callback?.(e, el);
            }
            catch (err) {
                logError("guardOnChange", err);
            }
        });
    };
    const csrfToken = () => document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "";
    const ajaxPost = (url, data, opts) => {
        if (isInvalidUrl(url)) {
            showToast(opts?.errorMsg || "Invalid URL", "error");
            return Promise.resolve({ ok: false, error: "Invalid URL" });
        }
        return safeFetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken(),
                ...(opts?.headers ?? {}),
            },
            body: JSON.stringify(data),
        });
    };
    window.RouteGuard = {
        init: initRouteGuards,
        showToast,
        scheduleInteractiveError,
        getMsg,
        attachGuard: attachRouteGuard,
        guardById,
        guardMultiple,
        guardFormSubmit,
        guardAllInContainer,
        guardOnChange,
        csrfToken,
        ajaxPost,
        isInvalidUrl,
        animations: { fadeIn, fadeOut, slideDown, slideUp, addAnimation },
        logError,
        safeFetch,
        TOAST_CONTAINER_ID,
        DATA_GUARD_MSG,
        DATA_SV_LOCALIZED,
        DATA_LISTENER_ACTIVE,
        DATA_FAILED_ROUTE,
    };
    if (document.readyState === "loading")
        document.addEventListener("DOMContentLoaded", initRouteGuards, {
            once: true,
        });
    else
        initRouteGuards();
})();
//# sourceMappingURL=route-guard.js.map