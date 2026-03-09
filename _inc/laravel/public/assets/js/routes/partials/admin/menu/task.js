/**
 * @file partials/admin/menu/task.js
 * @description Tasks menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#tasks-link");
  } catch (err) {
    console.error("Error initializing tasks menu guard:", err);
  }
})();
