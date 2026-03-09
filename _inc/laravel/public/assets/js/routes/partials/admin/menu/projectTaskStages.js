/**
 * @file partials/admin/menu/projectTaskStages.js
 * @description Project task stages menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#project-task-stages-index-link");
  } catch (err) {
    console.error("Error initializing project task stages menu guard:", err);
  }
})();
