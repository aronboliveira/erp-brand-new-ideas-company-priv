/**
 * Support Generate Route Guards
 * Handles AI support content generation link validation
 * @module routes/supports/generate
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#support-generate-ai", {
    fallbackMsg:
      "Generate support content route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
