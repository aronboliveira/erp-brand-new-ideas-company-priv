/**
 * @file Job Application Index Show Route Guard
 * @description Guards job application index breadcrumb link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#bc-job-application-index-link", {
    msgKey: "job_application_index_unavailable",
    fallbackMsg:
      "Job application index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
