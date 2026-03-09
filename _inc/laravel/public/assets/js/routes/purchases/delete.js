/**
 * @file Purchase Payment Delete Route Guard
 * @description Guards purchase payment delete links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.delete-purchase-payment", {
      fallbackMsg:
        "Destroy purchase payment route is unavailable. Please contact technical support or your domain administrator.",
      validateUrl: true,
    });
  } catch (_) {}
})();
