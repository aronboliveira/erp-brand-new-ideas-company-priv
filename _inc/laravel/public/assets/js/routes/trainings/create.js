/**
 * Trainings Create Route Guards
 * Handles training creation link validation
 * @module routes/trainings/create
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#training-create-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create training route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
