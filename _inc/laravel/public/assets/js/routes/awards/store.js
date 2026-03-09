/**
 * @file Award Store Route Guard
 * @description Guards the award creation form and generate link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#award-store-form", {
    msgKey: "store_award_unavailable",
    fallbackMsg:
      "Create award route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindClickGuard("#award-generate-link", {
    msgKey: "generate_award_unavailable",
    fallbackMsg:
      "Generate award route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
