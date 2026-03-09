/**
 * Project Task List Route Guards
 * Handles project task list link validation
 * @module routes/projects/tasks/list
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard(".project-task-index-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Show project task route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
