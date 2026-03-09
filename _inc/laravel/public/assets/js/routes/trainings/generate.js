/**
 * Training Generate Route Guards
 * Handles training generation link validation
 * @module routes/trainings/generate
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#training-generate-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Store training generate route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
