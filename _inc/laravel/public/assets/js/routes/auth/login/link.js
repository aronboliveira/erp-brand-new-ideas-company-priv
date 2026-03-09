/**
 * @file Login Link Route Guard
 * @description Guards the login link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#loginLink", {
    msgKey: "login_link_unavailable",
    fallbackMsg:
      "Login route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
