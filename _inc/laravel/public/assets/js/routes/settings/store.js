/**
 * @file Role Store Settings Route Guard
 * @description Guards the role creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#role-store-form", {
    msgKey: "store_role_unavailable",
    fallbackMsg:
      "Create role route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
