/**
 * @file Job Requirement Link Route Guard
 * @description Guards job requirement links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("a.job-requirement-link[data-url][data-guard-msg]", {
    msgKey: "job_requirement_unavailable",
    fallbackMsg:
      "Job requirement route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
