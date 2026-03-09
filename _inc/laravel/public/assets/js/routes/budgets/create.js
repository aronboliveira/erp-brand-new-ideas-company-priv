/**
 * @file Budget Create Route Guard
 * @description Guards budget planner creation button using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#budget-planner-create-btn", {
    msgKey: "create_budget_unavailable",
    fallbackMsg: "# ERROR",
  });
})();
