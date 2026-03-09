/**
 * @file Company Policies Index Route Guard
 * @description Guards company policy create/edit/delete links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }
    guard.bindClickGuard("#createPolicyBtn");
    guard.bindClickGuard('[data-listener-alias="edit-policy"]');
    guard.bindClickGuard('[data-listener-alias="delete-policy"]');
  } catch (err) {
    console.error("Error initializing company policies index guard:", err);
  }
})();
