/**
 * @file Job Index Breadcrumb Route Guard
 * @description Guards job index breadcrumb link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#bc-job-index-link", {
    msgKey: "job_index_unavailable",
    fallbackMsg:
      "Job index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
