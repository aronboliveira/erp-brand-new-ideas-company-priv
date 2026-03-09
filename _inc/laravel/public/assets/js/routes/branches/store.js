/**
 * @file Branch Store Route Guard
 * @description Guards the branch creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#store-branch-form", {
    msgKey: "store_branch_unavailable",
    fallbackMsg:
      "Create branch route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
