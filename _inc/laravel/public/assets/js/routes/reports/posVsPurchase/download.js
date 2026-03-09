/**
 * @file Reports POS vs Purchase Download Route Guard
 * @description Guards POS vs Purchase report download links with function check using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.download-pos-vs-purchase", {
      fallbackMsg:
        "Download function for POS vs Purchase report is unavailable. Please contact technical support or your domain administrator.",
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
          "Download function for POS vs Purchase report is unavailable. Please contact technical support or your domain administrator.";
        guard.showToast(msg);
      },
    });
  } catch (_) {}
})();
