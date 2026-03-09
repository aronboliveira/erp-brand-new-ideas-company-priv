/**
 * @file Purchase Payment Route Guard
 * @description Guards add purchase payment links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.add-purchase-payment", {
      fallbackMsg:
        "Add payment for purchase route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
