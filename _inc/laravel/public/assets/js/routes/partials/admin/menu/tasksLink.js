/**
 * @file partials/admin/menu/tasksLink.js
 * @description Tasks link menu guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#tasks-link");
  } catch (err) {
    console.error("Error initializing tasks link menu guard:", err);
  }
})();
