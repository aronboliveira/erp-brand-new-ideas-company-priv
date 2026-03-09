/**
 * @file Plans Index Route Guard
 * @description Guards plan links using ERPGuard singleton with auto-initialization and tooltip support
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
    });
  } catch (err) {
    console.error("Error initializing plans index guard:", err);
  }
})();
