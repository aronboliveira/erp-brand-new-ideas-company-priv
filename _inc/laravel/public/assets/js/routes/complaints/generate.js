/**
 * Complaint Generate Route Guards
 * Handles AI complaint generation link validation
 * @module routes/complaints/generate
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#ai-generate-complaint-link", {
    fallbackMsg:
      "Generate AI complaint route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
