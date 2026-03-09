/**
 * @file Purchase Sent Route Guard
 * @description Guards mark sent purchase links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.mark-sent-purchase", {
      fallbackMsg:
        "Sent purchase route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
