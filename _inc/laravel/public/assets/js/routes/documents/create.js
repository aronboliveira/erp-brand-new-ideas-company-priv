/**
 * Document Create Route Guards
 * Handles document creation button validation
 * @module routes/documents/create
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#document-create-btn", {
    fallbackMsg:
      "Create document route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
