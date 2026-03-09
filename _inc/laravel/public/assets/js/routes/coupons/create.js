/**
 * @file Coupon Create Route Guard
 * @description Guards the coupon creation button using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#coupon-create-btn", {
    msgKey: "create_coupon_unavailable",
    fallbackMsg:
      "Create coupon route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
