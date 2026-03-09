/**
 * @fileoverview Form submission guard for pipeline change using ERPGuard singleton
 * @module assets/js/routes/deals/pipelines/change
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#change-pipeline-form", {
      msg: btoa("# ERROR"),
    });
  } catch {}
})();
