/**
 * Contract Generate Route Guards
 * Handles AI contract generation link validation
 * @module routes/contracts/generate
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#ai-generate-contract-link", {
    fallbackMsg:
      "Generate AI contract route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
