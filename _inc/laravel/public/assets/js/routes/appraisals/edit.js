/**
 * @file Appraisal Edit Route Guard
 * @description Guards the appraisal update form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#appraisal-update-form", {
    msgKey: "update_appraisal_unavailable",
    fallbackMsg:
      "Update appraisal route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
