/**
 * @file partials/admin/menu/budgetPlanner.js
 * @description Budget planner menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#budget-planner-link");
  } catch (err) {
    console.error("Error initializing budget planner menu guard:", err);
  }
})();
