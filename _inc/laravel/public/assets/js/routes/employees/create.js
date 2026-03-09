/**
 * @file Employee Create Route Guard
 * @description Guards the employee creation button using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#employee-create-btn", {
    msgKey: "create_employee_unavailable",
    fallbackMsg:
      "Create employee route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
