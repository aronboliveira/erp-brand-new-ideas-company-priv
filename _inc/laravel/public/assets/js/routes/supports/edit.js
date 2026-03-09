/**
 * @file Support Edit Route Guard
 * @description Guards the support edit form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#edit-support-form", {
    msgKey: "edit_support_unavailable",
    fallbackMsg:
      "Update support route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
