/**
 * @file Customer Show Route Guard
 * @description Guards customer show buttons using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard('[id^="customer-show-btn-"]', {
    msgKey: "show_customer_unavailable",
    fallbackMsg:
      "Show customer route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
