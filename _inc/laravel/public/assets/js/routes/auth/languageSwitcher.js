/**
 * @file Auth Language Switcher Route Guard
 * @description Guards the language selector using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindChangeGuard("#language", {
    msgKey: "language_switch_unavailable",
    fallbackMsg:
      "Language switch route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
