/**
 * @file Appraisal Create Route Guard
 * @description Guards the appraisal creation link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#appraisal-create-link", {
    msgKey: "create_appraisal_unavailable",
    fallbackMsg:
      "Create appraisal route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
