/**
 * @file Reports Monthly Download Route Guard
 * @description Guards monthly report download links with function check using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard(".download-pdf-link", {
      fallbackMsg:
        "Download function for monthly reports is unavailable. Please contact technical support or your domain administrator.",
      handler(event, element) {
        event.preventDefault();
        const funcName = element.getAttribute("data-func-name") ?? "";
        const fn = window[funcName];
        if (typeof fn !== "function") {
          const msg =
            element.getAttribute("data-guard-msg") ??
            "Download function for monthly reports is unavailable. Please contact technical support or your domain administrator.";
          guard.showToast(msg);
          return;
        }
        fn();
      },
    });
  } catch (_) {}
})();
