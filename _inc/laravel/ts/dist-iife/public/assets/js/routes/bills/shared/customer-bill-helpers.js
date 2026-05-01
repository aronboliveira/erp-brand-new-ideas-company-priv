(function() {
"use strict";
/**
 * Customer Bill Page Helpers
 * Utilities for customer-facing bill pages that leverage ERPGuard.
 * Mirror of public/assets/js/routes/bills/shared/customer-bill-helpers.js
 * @requires window.ERPGuard (from core/erp-guard)
 */
const _emitted = {};
const devWarn = (tag, err) => {
    if (location.hostname !== "localhost" && location.hostname !== "127.0.0.1")
        return;
    const msg = err instanceof Error ? err.message : String(err);
    const key = `${tag}:${msg}`;
    if (_emitted[key])
        return;
    _emitted[key] = true;
    console.error(`[${tag}]`, msg);
};
(() => {
    "use strict";
    const DATA_LISTENER_ACTIVE = "data-listener-active", DATA_FAILED_ROUTE = "data-failed-route", DATA_GUARD_MSG = "data-guard-msg", ERR_FALLBACK = "# ERROR";
    const hasERPGuard = () => !!(window.ERPGuard && typeof window.ERPGuard.showToast === "function");
    const showToast = (msg, type = "error") => {
        if (window.ERPGuard && typeof window.ERPGuard.showToast === "function") {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
            window.ERPGuard.showToast(msg, type);
        }
        else {
            window.ERPGuard?.scheduleError?.(msg) ?? alert(msg);
        }
    };
    const isInvalidUrl = (url) => !url || url === "#";
    const guardPdfLink = (elementId) => {
        const link = document.getElementById(elementId);
        if (!link || link.getAttribute(DATA_LISTENER_ACTIVE) === "true")
            return;
        link.setAttribute(DATA_LISTENER_ACTIVE, "true");
        link.addEventListener("click", (event) => {
            try {
                const href = link.getAttribute("href"), url = link.getAttribute("data-url");
                if (!isInvalidUrl(href) || !isInvalidUrl(url))
                    return;
                event.preventDefault();
                const msg = link.getAttribute(DATA_GUARD_MSG) ?? ERR_FALLBACK;
                showToast(msg, "error");
                link.setAttribute(DATA_FAILED_ROUTE, "true");
            }
            catch (e) {
                devWarn("CustomerBillHelpers.guardPdfLink", e);
            }
        });
    };
    const guardQrCopy = (elementId, options = {}) => {
        const el = document.getElementById(elementId);
        if (!el || el.getAttribute(DATA_LISTENER_ACTIVE) === "true")
            return;
        el.setAttribute(DATA_LISTENER_ACTIVE, "true");
        el.addEventListener("click", (event) => {
            void (async () => {
                try {
                    const url = el.getAttribute("data-url");
                    if (isInvalidUrl(url)) {
                        event.preventDefault();
                        const msg = el.getAttribute(DATA_GUARD_MSG) ?? options.errorMsg ?? ERR_FALLBACK;
                        showToast(msg, "error");
                        el.setAttribute(DATA_FAILED_ROUTE, "true");
                        return;
                    }
                    if (navigator.clipboard && typeof navigator.clipboard.writeText === "function") {
                        await navigator.clipboard.writeText(url);
                        if (options.successMsg) {
                            showToast(options.successMsg, "success");
                        }
                    }
                }
                catch (e) {
                    devWarn("CustomerBillHelpers.guardQrCopy", e);
                }
            })();
        });
    };
    const initBillGuards = () => {
        document.querySelectorAll('[id^="bill-pdf-link-"]').forEach(el => {
            guardPdfLink(el.id);
        });
        document.querySelectorAll('[id^="bill-qr-copy-"]').forEach(el => {
            guardQrCopy(el.id);
        });
    };
    const ensureRouteGuardInit = () => {
        if (hasERPGuard() && typeof window.ERPGuard?.init === "function") {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access
            window.ERPGuard.init();
        }
    };
    window.CustomerBillHelpers = {
        showToast,
        isInvalidUrl,
        guardPdfLink,
        guardQrCopy,
        initBillGuards,
        ensureRouteGuardInit,
        hasERPGuard,
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", initBillGuards, {
            once: true,
        })
        : initBillGuards();
})();
})();