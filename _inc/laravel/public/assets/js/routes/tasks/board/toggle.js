/**
 * Tasks Board Toggle Route Guards
 * Handles task board view toggle links (list/grid)
 * @module routes/tasks/board/toggle
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#task-view-toggle-list", {
    fallbackMsg:
      "Task board view route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindClickGuard("#task-view-toggle-grid", {
    fallbackMsg:
      "Task board view route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
