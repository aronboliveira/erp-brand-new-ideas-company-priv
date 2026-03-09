/**
 * @file Reports Monthly Save Route Guard
 * @description Guards monthly POS download links with function check using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard(".download-monthly-pos-link", {
      fallbackMsg:
        "Download function is unavailable. Please contact technical support or your domain administrator.",
      handler(event, element) {
        const funcName = element.getAttribute("data-func-name") ?? "";
        if (!funcName) return;
        event.preventDefault();
        const fn = window[funcName];
        if (typeof fn !== "function") {
          const msg =
            element.getAttribute("data-guard-msg") ??
            "Download function is unavailable. Please contact technical support or your domain administrator.";
          guard.showToast(msg);
          return;
        }
        fn();
      },
    });
  } catch (_) {}
})();
