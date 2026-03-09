/**
 * @file Purchase Product Route Guard
 * @description Guards purchase product select changes using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindChangeGuard("select.item", {
      fallbackMsg:
        "Purchase product route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
