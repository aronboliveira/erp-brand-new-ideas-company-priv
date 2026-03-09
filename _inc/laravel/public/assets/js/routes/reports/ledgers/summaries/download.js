/**
 * @file Reports Ledger Summary Download Route Guard
 * @description Guards ledger summary download links with function check using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.download-ledger-summary", {
      fallbackMsg:
        "Download function for ledger summaries is unavailable. Please contact technical support or your domain administrator.",
      handler(event, element) {
        event.preventDefault();
        const fnName = element.getAttribute("data-func-name") || "";
        const fn =
          typeof window !== "undefined" && fnName ? window[fnName] : null;
        if (typeof fn === "function") {
          try {
            fn();
          } catch (_) {}
          return;
        }
        const msg =
          element.getAttribute("data-guard-msg") ||
          "Download function for ledger summaries is unavailable. Please contact technical support or your domain administrator.";
        guard.showToast(msg);
      },
    });
  } catch (_) {}
})();
