/**
 * @file Employee Destroy Route Guard
 * @description Guards the employee delete buttons using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard(
    'a[id^="delete-employee-btn-"][data-url][data-guard-msg]',
    {
      msgKey: "delete_employee_unavailable",
      fallbackMsg:
        "Delete employee route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
