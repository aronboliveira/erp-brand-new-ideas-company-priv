/**
 * @fileoverview TypeScript version of public/assets/js/routes/contracts/shared/helpers.js
 * @generated from original JavaScript - manual review recommended
 * @module helpers
 */
/** @requires ERPGuard, ERPUtils */
/**
 * Shared helper functions for contracts module
 * Extracted from inline scripts in show.blade.php
 * Delegates to ERPGuard/ERPUtils singletons where possible.
 * @module contracts/shared/helpers
 */
(function (global) {
    "use strict";
    // Avoid re-initialization
    if (global.ContractHelpers)
        return;
    const ERPBootstrap = global.ERPBootstrap;
    const { guard } = ERPBootstrap
        ? (ERPBootstrap.require?.("ERPGuard", "ERPUtils") ?? { guard: null })
        : { guard: null };
    const ERR_FALLBACK = "# ERROR", DATA_CLIENT_LOCALIZED = "data-client-localized", DATA_GUARD_MSG = "data-guard-msg", DATA_SV_LOCALIZED = "data-sv-localized";
    /** @param {string} s @param {Element|Document} r @returns {Element|null} */
    const qs = (s, r = document) => r.querySelector(s);
    /** @param {string} s @param {Element|Document} r @returns {NodeList} */
    const qsa = (s, r = document) => r.querySelectorAll(s);
    /** Delegates to guard.hasBootstrap() */
    const hasBootstrap = () => (guard ? guard.hasBootstrap() : false);
    /** Kept for backward-compat API; guard handles toasts internally */
    const ensureToastContainer = (id = "np-toast-container") => {
        const stringId = String(id);
        let c = qs("#" + stringId);
        if (c)
            return c;
        c = document.createElement("div");
        c.id = stringId;
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        Object.assign(c.style, {
            position: "fixed",
            top: "1rem",
            right: "1rem",
            zIndex: "9999",
        });
        document.body.appendChild(c);
        return c;
    };
    /** Delegates to guard.error() */
    const showError = (message) => {
        if (guard) {
            guard.error(message ?? ERR_FALLBACK);
        }
        else {
            alert(message ?? ERR_FALLBACK);
        }
    };
    /** Delegates to guard.showToast() */
    const svToastOrAlert = (msg) => {
        if (guard) {
            guard.showToast(msg, "danger");
        }
        else {
            alert(msg);
        }
    };
    /** Delegates to guard.scheduleInteractiveError() */
    const scheduleErrorOnEvent = (msg) => {
        if (guard) {
            guard.scheduleInteractiveError(msg);
        }
        else {
            alert(msg);
        }
    };
    /** Delegates to guard.getMsg() */
    const getLocalizedMessage = (_el, key) => {
        if (guard)
            return guard.getMsg(key) || ERR_FALLBACK;
        return ERR_FALLBACK;
    };
    /** Resolves URL from element data-url/href. Delegates to guard.resolveUrl() */
    const verifyRouteFromElement = (el) => {
        if (guard)
            return guard.resolveUrl(el) ?? "";
        const url = el?.getAttribute("data-url"), href = el?.getAttribute("href");
        if ((!url || url === "#") && (!href || href === "#"))
            return "";
        return url && url !== "#" ? url : href && href !== "#" ? href : "";
    };
    /** Verifies URL validity. Delegates to guard.isInvalidUrl() */
    const verifyRoute = (candidate) => guard ? !guard.isInvalidUrl(candidate) : !!(candidate && candidate !== "#");
    /** Delegates to guard.getCsrfToken() */
    const getToken = () => guard
        ? guard.getCsrfToken()
        : (document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") ?? "");
    /**
     * Checks if jQuery is available
     * @param {Function} onError - Callback on error
     * @returns {boolean}
     */
    const ensureJQuery = (onError) => {
        const $ = global.jQuery;
        if (!$?.fn) {
            try {
                if (isLocalhost())
                    console.error("jQuery unavailable");
            }
            catch (_) {
                console.error(`[helpers] Error:`, _);
            }
            if (typeof onError === "function")
                onError();
            return false;
        }
        return true;
    };
    /**
     * Checks if Dropzone is available
     * @param {Function} onError - Callback on error
     * @returns {boolean}
     */
    const ensureDropzone = (onError) => {
        if (!global.Dropzone) {
            try {
                if (isLocalhost())
                    console.error("Dropzone unavailable");
            }
            catch (_) {
                console.error(`[helpers] Error:`, _);
            }
            if (typeof onError === "function")
                onError();
            return false;
        }
        return true;
    };
    /**
     * Checks if running on localhost
     * @returns {boolean}
     */
    const isLocalhost = () => global.location.hostname === "localhost" ||
        global.location.hostname === "127.0.0.1";
    /**
     * Guards anchor by href - prevents click if invalid
     * @param {Element} anchor - Anchor element
     * @param {boolean} useDataUrl - Check data-url instead of href
     */
    const guardAnchor = (anchor, useDataUrl = false) => {
        if (!anchor)
            return;
        const msg = anchor.getAttribute("data-guard-msg") ?? "This action is unavailable.";
        const attr = useDataUrl ? "data-url" : "href", value = (anchor.getAttribute(attr) ?? "").trim();
        if (!value || value === "#")
            if (!anchor.getAttribute("data-listener-bound-click")) {
                anchor.setAttribute("data-listener-bound-click", "1");
                anchor.addEventListener("click", (e) => {
                    e.preventDefault();
                    svToastOrAlert(msg);
                });
            }
    };
    /**
     * Guards form action - prevents submit if invalid
     * @param {string} formSelector - Form CSS selector
     * @param {string} anchorSelector - Anchor inside form selector
     */
    const guardFormAction = (formSelector, anchorSelector) => {
        const forms = document.querySelectorAll(formSelector);
        Array.prototype.forEach.call(forms, (form) => {
            const action = (form.getAttribute("action") ?? "").trim();
            if (!action || action === "#") {
                const anchor = form.querySelector(anchorSelector);
                if (!anchor)
                    return;
                const msg = anchor.getAttribute("data-guard-msg") ??
                    "This action is unavailable.";
                if (!anchor.getAttribute("data-listener-bound-click")) {
                    anchor.setAttribute("data-listener-bound-click", "1");
                    anchor.addEventListener("click", (e) => {
                        e.preventDefault();
                        svToastOrAlert(msg);
                    });
                }
            }
        });
    };
    /**
     * Creates a bound event handler with cleanup observer
     * @param {string} bindAttr - Data attribute to mark as bound
     * @param {Function} setup - Setup function
     * @param {Function} cleanup - Cleanup function
     */
    const createBoundHandler = (bindAttr, setup, cleanup) => {
        const host = document.body;
        if (host.getAttribute(bindAttr) === "true")
            return;
        host.setAttribute(bindAttr, "true");
        setup();
        const mo = new MutationObserver(() => {
            if (cleanup())
                host.removeAttribute(bindAttr);
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    // Export to global namespace
    global.ContractHelpers = {
        // Constants
        ERR_FALLBACK,
        DATA_CLIENT_LOCALIZED,
        DATA_GUARD_MSG,
        DATA_SV_LOCALIZED,
        // DOM utilities
        qs,
        qsa,
        // Bootstrap utilities
        hasBootstrap,
        ensureToastContainer,
        showError,
        svToastOrAlert,
        scheduleErrorOnEvent,
        // Localization
        getLocalizedMessage,
        getMsg: getLocalizedMessage, // alias
        // Route utilities
        verifyRouteFromElement,
        verifyRoute,
        getToken,
        // Plugin checks
        ensureJQuery,
        ensureDropzone,
        isLocalhost,
        // Guards
        guardAnchor,
        guardFormAction,
        // Binding utilities
        createBoundHandler,
    };
    // Also expose svToastOrAlert globally for backward compatibility
    if (!global.svToastOrAlert)
        global.svToastOrAlert = svToastOrAlert;
})(typeof window !== "undefined" ? window : this);
//# sourceMappingURL=helpers.js.map