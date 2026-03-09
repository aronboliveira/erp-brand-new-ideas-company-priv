/**
 * Users Open Route Guards
 * Handles user index open link
 * @module routes/users/open
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#user-index-open", {
    fallbackMsg:
      "User route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
