/**
 * @file Award Edit Route Guard
 * @description Guards the award update form and generate link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#award-update-form", {
    msgKey: "update_award_unavailable",
    fallbackMsg:
      "Update award route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindClickGuard("#award-generate-link", {
    msgKey: "generate_award_unavailable",
    fallbackMsg:
      "Generate award route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
