/**
 * @file Purchase Cancel Route Guard
 * @description Guards purchase cancel button using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("#purchase-cancel-btn", {
      fallbackMsg:
        "Purchase index route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
