/**
 * Company Policy Generate Route Guards
 * Handles AI company policy generation link validation
 * @module routes/companyPolicies/generate
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#ai-generate-company-policy-link", {
    fallbackMsg:
      "Generate AI company policy route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
