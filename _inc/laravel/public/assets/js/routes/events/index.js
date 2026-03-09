/**
 * @file Events Index Route Guard
 * @description Guards event links and forms using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }
    guard.bindClickGuard("#events-create-link");
    guard.bindClickGuard("[id^='events-edit-title-link-']");
    guard.bindClickGuard("[id^='events-edit-icon-link-']");
    guard.bindClickGuard("[id^='events-delete-link-']");
    guard.bindSubmitGuard("form[id^='events-delete-form-']");
  } catch (err) {
    console.error("Error initializing events index guard:", err);
  }
})();
