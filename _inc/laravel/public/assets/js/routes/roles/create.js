/**
 * Roles Create Route Guards
 * Handles role creation link validation with querySelectorAll
 * @module routes/roles/create
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.role-create", {
    fallbackMsg:
      "Create role route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
