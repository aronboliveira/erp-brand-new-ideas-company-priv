/**
 * @file Performance Types Index Route Guard
 * @description Guards performance type links/forms and initializes confirm modals using ERPGuard singleton
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
    console.error("Error initializing performance types index guard:", err);
  }
})();
