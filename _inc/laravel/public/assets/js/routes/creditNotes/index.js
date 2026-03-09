/**
 * @file Credit Notes Index Route Guard
 * @description Guards credit note create/edit/delete links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }
    guard.bindClickGuard("#create_credit_note_btn");
    guard.bindClickGuard('[data-listener-alias="edit-credit-note"]');
    guard.bindClickGuard('[data-listener-alias="delete-credit-note"]');
  } catch (err) {
    console.error("Error initializing credit notes index guard:", err);
  }
})();
