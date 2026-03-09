/**
 * @fileoverview Route guards for project report reset using ERPGuard singleton
 * @module assets/js/routes/projects/reports/reset
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#project_report_submit", {
      msg: btoa(
        "Project report index route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
    window.ERPGuard?.bindClickGuard?.(".reset-project-report-link", {
      msg: btoa(
        "Project report index route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
