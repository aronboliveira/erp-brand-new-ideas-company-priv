/**
 * @file User Update Route Guard
 * @description Guards user update forms using ERPGuard singleton
 * @requires ERPGuard
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("form[data-resolved-action][data-guard-msg]", {
    msgKey: "update_user_unavailable",
    fallbackMsg:
      "Update user route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
