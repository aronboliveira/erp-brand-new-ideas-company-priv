/**
 * @file Allowance Options Index Route Guard
 * @description Guards allowance option links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }
    guard.bindClickGuard('[data-sv-localized="true"]');
  } catch (err) {
    console.error("Error initializing allowance options index guard:", err);
  }
})();
