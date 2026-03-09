/**
 * @fileoverview Form submission guard for project task comment storage using ERPGuard singleton
 * @module assets/js/routes/projects/tasks/comments/store
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#form-comment", {
      msg: btoa(
        "Store project task comment route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
