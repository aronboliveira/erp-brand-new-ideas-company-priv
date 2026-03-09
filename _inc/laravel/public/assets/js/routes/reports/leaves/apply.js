/**
 * Leave Report Apply Route Guards
 * Handles leave report apply button with form submission
 * @module routes/reports/leaves/apply
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.apply-leave-report", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    fallbackMsg:
      "Leave report route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
