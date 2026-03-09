/**
 * @file Journal Entries View Route Guard
 * @description Guards journal entry view links/forms and initializes tooltips using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }

    document.addEventListener("DOMContentLoaded", () => {
      guard.initTooltips();
      guard.bindClickGuard("a[data-guard-msg], a[data-url]");
      guard.bindSubmitGuard("form[data-guard-msg], form[data-url]");
    });
  } catch (err) {
    console.error("Error initializing journalEntries/view guard:", err);
  }
})();
