/**
 * Project Task Stages Create Route Guards
 * Handles project task stage creation link
 * @module routes/projectTaskStages/create
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#{{ $taskStageCreateAnchorId }}", {
    fallbackMsg:
      "Create project task stage route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
