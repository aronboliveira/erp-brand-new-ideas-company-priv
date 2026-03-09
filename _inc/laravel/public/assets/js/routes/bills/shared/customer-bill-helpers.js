/**
 * Customer Bill Page Helpers
 * Provides utilities for customer-facing bill pages that leverage ERPGuard.
 * @requires window.ERPGuard (from core/erp-guard.js)
 */
(function () {
  "use strict";

  const DATA_LISTENER_ACTIVE = "data-listener-active";
  const DATA_FAILED_ROUTE = "data-failed-route";
  const DATA_GUARD_MSG = "data-guard-msg";
  const ERR_FALLBACK = "# ERROR";

  /**
   * Check if ERPGuard is available
   * @returns {boolean}
   */
  const hasERPGuard = () =>
    !!(window.ERPGuard && typeof window.ERPGuard.showToast === "function");

  /**
   * Show toast message using ERPGuard or fallback
   * @param {string} msg - Message to display
   * @param {string} [type="error"] - Toast type (error, success, warning)
   */
  const showToast = (msg, type = "error") => {
    if (hasERPGuard()) {
      window.ERPGuard.showToast(msg, type);
    } else {
      window.ERPGuard?.scheduleError?.(msg, "click") ?? alert(msg);
    }
  };

  /**
   * Check if URL is invalid (empty or "#")
   * @param {string|null|undefined} url
   * @returns {boolean}
   */
  const isInvalidUrl = url => !url || url === "#";

  /**
   * Attach a download/PDF link guard to an element
   * Shows error toast when route is unavailable
   * @param {string} elementId - Element ID to guard
   */
  const guardPdfLink = elementId => {
    const link = document.getElementById(elementId);
    if (!link || link.getAttribute(DATA_LISTENER_ACTIVE) === "true") return;

    link.setAttribute(DATA_LISTENER_ACTIVE, "true");

    link.addEventListener("click", event => {
      try {
        const href = link.getAttribute("href");
        const url = link.getAttribute("data-url");

        if (!isInvalidUrl(href) || !isInvalidUrl(url)) return;

        event.preventDefault();
        const msg = link.getAttribute(DATA_GUARD_MSG) ?? ERR_FALLBACK;
        showToast(msg, "error");
        link.setAttribute(DATA_FAILED_ROUTE, "true");
      } catch (e) {
        console.error("[CustomerBillHelpers] guardPdfLink error:", e);
      }
    });
  };

  /**
   * Attach a QR code click handler that copies URL to clipboard
   * Shows error toast when route is unavailable
   * @param {string} elementId - Element ID of QR container
   * @param {Object} [options] - Configuration options
   * @param {string} [options.successMsg] - Message on successful copy
   * @param {string} [options.errorMsg] - Message when route unavailable
   */
  const guardQrCopy = (elementId, options = {}) => {
    const el = document.getElementById(elementId);
    if (!el || el.getAttribute(DATA_LISTENER_ACTIVE) === "true") return;

    el.setAttribute(DATA_LISTENER_ACTIVE, "true");

    el.addEventListener("click", async event => {
      try {
        const url = el.getAttribute("data-url");

        if (isInvalidUrl(url)) {
          event.preventDefault();
          const msg =
            el.getAttribute(DATA_GUARD_MSG) ?? options.errorMsg ?? ERR_FALLBACK;
          showToast(msg, "error");
          el.setAttribute(DATA_FAILED_ROUTE, "true");
          return;
        }

        if (
          navigator.clipboard &&
          typeof navigator.clipboard.writeText === "function"
        ) {
          await navigator.clipboard.writeText(url);
          if (options.successMsg) {
            showToast(options.successMsg, "success");
          }
        }
      } catch (e) {
        console.error("[CustomerBillHelpers] guardQrCopy error:", e);
      }
    });
  };

  /**
   * Initialize all guards on the page by scanning for known patterns
   * Looks for elements with specific ID patterns
   */
  const initBillGuards = () => {
    // Guard PDF download links (pattern: bill-pdf-link-{id})
    document.querySelectorAll('[id^="bill-pdf-link-"]').forEach(el => {
      guardPdfLink(el.id);
    });

    // Guard QR copy elements (pattern: bill-qr-copy-{id})
    document.querySelectorAll('[id^="bill-qr-copy-"]').forEach(el => {
      guardQrCopy(el.id);
    });
  };

  /**
   * Initialize RouteGuard if available but not yet initialized
   */
  const ensureRouteGuardInit = () => {
    if (hasRouteGuard() && typeof window.RouteGuard.init === "function") {
      // RouteGuard auto-initializes, but we can call init for dynamic content
      window.RouteGuard.init();
    }
  };

  // Expose API
  window.CustomerBillHelpers = {
    showToast,
    isInvalidUrl,
    guardPdfLink,
    guardQrCopy,
    initBillGuards,
    ensureRouteGuardInit,
    hasRouteGuard,
  };

  // Auto-initialize on DOM ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initBillGuards, {
      once: true,
    });
  } else {
    initBillGuards();
  }
})();
