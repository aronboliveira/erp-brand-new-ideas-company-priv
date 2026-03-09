/**
 * Job Category Edit Route Guards
 * Handles job category edit form validation
 * @module routes/jobs/categories/edit
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#jobCategory-edit-form", {
    fallbackMsg: "Route unavailable.",
  });
})();
