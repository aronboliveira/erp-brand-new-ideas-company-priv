/**
 * @file Language Store Route Guard
 * @description Guards the language creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#language-create-form", {
    msgKey: "create_language_unavailable",
    fallbackMsg:
      "Create language route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
