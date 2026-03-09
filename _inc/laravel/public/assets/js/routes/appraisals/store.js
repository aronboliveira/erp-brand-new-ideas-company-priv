/**
 * @file Appraisal Store Route Guard
 * @description Guards the appraisal creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#appraisal-store-form", {
    msgKey: "store_appraisal_unavailable",
    fallbackMsg:
      "Create appraisal route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
