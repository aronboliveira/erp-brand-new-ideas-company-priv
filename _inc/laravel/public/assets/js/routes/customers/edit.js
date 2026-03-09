/**
 * @file Customer Edit Route Guard
 * @description Guards customer edit buttons using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard('[id^="customer-edit-btn-"]', {
    msgKey: "edit_customer_unavailable",
    fallbackMsg:
      "Edit customer route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
