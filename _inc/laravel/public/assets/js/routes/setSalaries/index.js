/**
 * Set Salaries Index Route Guards
 * Handles salary view and set links with dynamic IDs
 * @module routes/setSalaries/index
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard('[id^="salary-view-"]', {
    fallbackMsg:
      "Salary view route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindClickGuard('[id^="salary-set-"]', {
    fallbackMsg:
      "Salary set route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
