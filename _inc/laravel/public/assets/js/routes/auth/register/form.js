/**
 * @file Register Form Route Guard
 * @description Guards the registration form and register link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#register-form", {
    msgKey: "register_route_unavailable",
    fallbackMsg:
      "Registration is unavailable. Please contact technical support or your domain administrator.",
  });
})();
