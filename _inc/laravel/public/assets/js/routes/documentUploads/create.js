/**
 * Document Uploads Create Route Guards
 * Handles document upload button validation
 * @module routes/documentUploads/create
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#document-create-btn", {
    fallbackMsg:
      "Create document route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
