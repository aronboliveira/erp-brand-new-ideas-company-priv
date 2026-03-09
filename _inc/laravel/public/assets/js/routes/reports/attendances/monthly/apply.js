/**
 * Monthly Attendance Report Apply Route Guards
 * Handles monthly attendance report apply button with form submission
 * @module routes/reports/attendances/monthly/apply
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.apply-monthly-attendance", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    fallbackMsg:
      "Monthly attendance apply route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
