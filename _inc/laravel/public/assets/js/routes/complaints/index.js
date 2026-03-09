/**
 * @file Complaints Index Route Guard
 * @description Guards complaint create/edit/delete links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }
    guard.bindClickGuard("#createComplaintBtn");
    guard.bindClickGuard('[data-listener-alias="edit-complaint"]');
    guard.bindClickGuard('[data-listener-alias="delete-complaint"]');
  } catch (err) {
    console.error("Error initializing complaints index guard:", err);
  }
})();
