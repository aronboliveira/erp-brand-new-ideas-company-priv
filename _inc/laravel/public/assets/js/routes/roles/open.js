/**
 * Roles Open Route Guards
 * Handles roles index open link
 * @module routes/roles/open
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#roles-index-open", {
    fallbackMsg:
      "Role route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
