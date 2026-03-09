/**
 * @file Payments Index Route Guard
 * @description Guards payment links/forms and initializes tooltips using ERPGuard singleton
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
      guard.initTooltips();
    });
  } catch (err) {
    console.error("Error initializing payments index guard:", err);
  }
})();
