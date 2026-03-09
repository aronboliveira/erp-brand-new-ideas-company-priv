/**
 * @file Jobs Index Route Guard
 * @description Guards jobs routes, handles clipboard copy, and initializes tooltips using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  const utils = window.ERPUtils;
  const T = window.JOBS_I18N || {};
  const COPIED = T.copySuccess || "Link copied to clipboard";
  const COPY_FAIL = T.copyFail || "Failed to copy link";

  const initTooltips = () => {
    try {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        try {
          window.bootstrap?.Tooltip.getOrCreateInstance(el);
        } catch {}
      });
    } catch {}
  };

  const bindCopy = () => {
    document.querySelectorAll("a.copy-link").forEach(a => {
      if (a.getAttribute("data-copy-bound") === "true") return;
      a.setAttribute("data-copy-bound", "true");
      a.addEventListener("click", e => {
        const href = (a.getAttribute("href") ?? "#").trim();
        const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
        if (url === "#" || href === "#") {
          e.preventDefault();
          guard.showToast(
            a.getAttribute("data-guard-msg") || "Route unavailable",
          );
          return;
        }
        e.preventDefault();
        if (utils) {
          utils
            .copyToClipboard(url, false)
            .then(() => guard.showToast(COPIED, "success"))
            .catch(() => guard.showToast(COPY_FAIL, "error"));
        } else {
          navigator.clipboard
            ?.writeText(url)
            .then(() => guard.showToast(COPIED, "success"))
            .catch(() => guard.showToast(COPY_FAIL, "error"));
        }
      });
    });
  };

  document.addEventListener("DOMContentLoaded", () => {
    guard.bindClickGuard("a.route-guard, a[data-guard-msg], a[data-url]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        T.routeUnavailable ||
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    guard.bindSubmitGuard("form[data-guard-msg], form[data-url]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        T.routeUnavailable ||
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    bindCopy();
    initTooltips();
  });
})();
