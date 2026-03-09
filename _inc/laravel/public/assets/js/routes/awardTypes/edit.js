/**
 * @file Award Type Edit Route Guard
 * @description Guards the award type update form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#awardtype-update-form", {
    msgKey: "update_award_type_unavailable",
    fallbackMsg:
      "Update award type route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
