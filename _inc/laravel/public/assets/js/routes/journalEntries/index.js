/**
 * @file Journal Entries Index Route Guard
 * @description Guards journal entries links and forms using ERPGuard singleton
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
    console.error("Error initializing journal entries index guard:", err);
  }
})();
