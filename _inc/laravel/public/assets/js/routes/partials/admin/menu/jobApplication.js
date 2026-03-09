/**
 * @file partials/admin/menu/jobApplication.js
 * @description Job application menu links guard using ERPGuard singleton (8 links)
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#job-index-link");
    window.ERPGuard.bindClickGuard("#job-create-link");
    window.ERPGuard.bindClickGuard("#job-application-link");
    window.ERPGuard.bindClickGuard("#job-candidate-link");
    window.ERPGuard.bindClickGuard("#job-on-board-link");
    window.ERPGuard.bindClickGuard("#custom-question-link");
    window.ERPGuard.bindClickGuard("#interview-schedule-link");
    window.ERPGuard.bindClickGuard("#career-index-link");
  } catch (err) {
    console.error("Error initializing job application menu guards:", err);
  }
})();
