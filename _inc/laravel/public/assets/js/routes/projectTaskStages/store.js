/**
 * @file Project Task Stage Store Route Guard
 * @description Guards the project task stage creation form using ERPGuard singleton
 * @requires ERPGuard
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#store-project-task-stage-form", {
    msgKey: "store_project_task_stage_unavailable",
    fallbackMsg:
      "Store project task stage route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
