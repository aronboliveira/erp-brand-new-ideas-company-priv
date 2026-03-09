/**
 * @file Pipeline Store Route Guard
 * @description Guards the pipeline creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#ppl-store-form", {
    msgKey: "store_pipeline_unavailable",
    fallbackMsg:
      "Pipeline store route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
