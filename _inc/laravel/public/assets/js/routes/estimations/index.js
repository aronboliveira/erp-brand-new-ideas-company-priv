/**
 * @file Estimations Index Route Guard
 * @description Guards estimation create button and links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }
    guard.bindClickGuard("#est-create-btn");
    guard.bindClickGuard("a[data-guard-msg]");
  } catch (err) {
    console.error("Error initializing estimations index guard:", err);
  }
})();
