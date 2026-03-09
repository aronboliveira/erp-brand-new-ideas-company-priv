/**
 * Training Types Create Route Guards
 * Handles training type creation link validation
 * @module routes/trainings/types/create
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#training-type-create-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create training type route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
