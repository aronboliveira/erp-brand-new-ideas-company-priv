/**
 * @file Job Apply Requirement Route Guard
 * @description Guards job apply links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("a.job-apply-link[data-url][data-guard-msg]", {
    msgKey: "apply_job_unavailable",
    fallbackMsg:
      "Apply job route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
