/**
 * @file Customer Delete Route Guard
 * @description Guards customer delete buttons using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard('[id^="customer-delete-btn-"]', {
    msgKey: "delete_customer_unavailable",
    fallbackMsg:
      "Delete customer route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
