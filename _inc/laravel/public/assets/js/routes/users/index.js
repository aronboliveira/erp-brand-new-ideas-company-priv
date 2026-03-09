/**
 * Users Index Route Guards
 * Handles user logs and create user link validation
 * @module routes/users/index
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#users-log-link", {
    fallbackMsg:
      "User logs route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard("#user-create-link", {
    fallbackMsg:
      "Create user route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
