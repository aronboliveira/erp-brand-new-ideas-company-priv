/**
 * @file Pipelines Index Route Guard
 * @description Guards pipeline links/forms and initializes confirm modals using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }
    document.addEventListener("DOMContentLoaded", () => {
      guard.bindClickGuard("a[data-guard-msg], a[data-url]");
      guard.bindSubmitGuard("form[data-guard-msg], form[data-url]");
      guard.initConfirmModals(".bs-pass-para");
      guard.initTooltips();
    });
  } catch (err) {
    console.error("Error initializing pipelines index guard:", err);
  }
})();
