/**
 * Project Tasks Index Route Guards
 * Handles project task index link validation
 * @module routes/projects/tasks/index
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
