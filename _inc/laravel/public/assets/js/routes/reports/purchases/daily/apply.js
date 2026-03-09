/**
 * Daily Purchase Report Apply Route Guards
 * Handles daily purchase report apply button with form submission
 * @module routes/reports/purchases/daily/apply
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.apply-daily-purchase-link", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    fallbackMsg:
      "Daily purchase apply route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
