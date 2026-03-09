/**
 * Settings Roles Cancel Route Guards
 * Handles role index cancel link
 * @module routes/settings/roles/cancel
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#role-index-cancel-link", {
    fallbackMsg:
      "Role index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
