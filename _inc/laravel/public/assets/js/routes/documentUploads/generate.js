/**
 * Document Upload Generate Route Guards
 * Handles document generation button validation
 * @module routes/documentUploads/generate
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#document-generate-btn", {
    fallbackMsg:
      "Generate document route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
