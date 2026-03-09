/**
 * @file Form Builders Index Route Guard
 * @description Guards form builder links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }
    guard.bindClickGuard("a[data-guard-msg], a[data-url]");
  } catch (err) {
    console.error("Error initializing form builders index guard:", err);
  }
})();
