/**
 * @file Register Form Route Guard
 * @description Guards the registration form using ERPGuard singleton
 * Mirror of public/assets/js/routes/auth/register/form.js
 */

(() => {
  const guard = (window as any).ERPGuard as
    | { bindSubmitGuard(sel: string, opts: { msgKey: string; fallbackMsg: string }): void }
    | undefined;
  if (!guard) return;

  guard.bindSubmitGuard("#register-form", {
    msgKey: "register_route_unavailable",
    fallbackMsg:
      "Registration is unavailable. Please contact technical support or your domain administrator.",
  });
})();

export {};
