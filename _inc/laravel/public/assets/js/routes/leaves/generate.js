/**
 * Leave Generate Route Guards
 * Handles AI leave content generation link validation
 * @module routes/leaves/generate
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#leave-ai-generate-link", {
    fallbackMsg:
      "Generate leave content route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
