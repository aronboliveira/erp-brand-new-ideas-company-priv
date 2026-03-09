/**
 * @file Award Type Store Route Guard
 * @description Guards the award type creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#awardtype-store-form", {
    msgKey: "store_award_type_unavailable",
    fallbackMsg:
      "Create award type route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
