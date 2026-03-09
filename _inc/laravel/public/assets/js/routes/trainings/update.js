/**
 * @file Training Update Route Guard
 * @description Guards the training update form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#edit_training", {
    msgKey: "update_training_unavailable",
    fallbackMsg:
      "Update training route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
