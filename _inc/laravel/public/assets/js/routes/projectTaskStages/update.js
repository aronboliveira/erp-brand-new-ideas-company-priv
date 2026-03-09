/**
 * @fileoverview Form submission guard for project task stage update using ERPGuard singleton
 * @module assets/js/routes/projectTaskStages/update
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#update-project-task-stage-form", {
      msg: btoa(
        "Update project task stage route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
