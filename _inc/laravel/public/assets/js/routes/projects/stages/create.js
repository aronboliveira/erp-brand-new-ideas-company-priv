/**
 * @file Project Stage Create Route Guard
 * @description Guards the project stage creation link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#project-stage-create-link", {
    msgKey: "create_project_stage_unavailable",
    fallbackMsg:
      "Create project stage route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
