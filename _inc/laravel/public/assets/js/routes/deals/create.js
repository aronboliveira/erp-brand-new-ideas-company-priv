/**
 * @file Deal Create Route Guard
 * @description Guards the deal creation button using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#deal-create-btn", {
    msgKey: "create_deal_unavailable",
    fallbackMsg:
      "Create deal route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
