/**
 * @file partials/admin/menu/financialGoal.js
 * @description Financial goal index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#financial-goal-index-link");
  } catch (err) {
    console.error("Error initializing financial goal menu guard:", err);
  }
})();
