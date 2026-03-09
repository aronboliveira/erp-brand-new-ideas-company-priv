/**
 * @file Leaves Index Route Guard
 * @description Guards leave links/forms and initializes tooltips using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }
    guard.bindClickGuard("a[data-guard-msg], a[data-url]");
    guard.bindSubmitGuard("form[data-guard-msg], form[data-url]");
    guard.initTooltips();
  } catch (err) {
    console.error("Error initializing leaves index guard:", err);
  }
})();
