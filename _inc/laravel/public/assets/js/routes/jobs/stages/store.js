/**
 * Job Stage Store Route Guards
 * Handles job stage store form validation
 * @module routes/jobs/stages/store
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#jb-stg-store-form", {
    fallbackMsg:
      "Job stage store route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
