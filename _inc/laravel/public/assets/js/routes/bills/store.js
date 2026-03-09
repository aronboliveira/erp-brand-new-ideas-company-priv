/**
 * @file Bill Store Route Guard
 * @description Guards the bill creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#bills-store-form", {
    msgKey: "store_bill_unavailable",
    fallbackMsg:
      "Create bill route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
