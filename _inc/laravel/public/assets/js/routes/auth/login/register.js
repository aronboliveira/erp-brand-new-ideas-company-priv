/**
 * @file Login Register Route Guard
 * @description Guards the user registration form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#register-form", {
    msgKey: "register_unavailable",
    fallbackMsg:
      "User registration route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
