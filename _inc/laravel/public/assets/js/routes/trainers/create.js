/**
 * @file Trainer Create Route Guard
 * @description Guards the trainer creation link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#trainer-create-link", {
    msgKey: "create_trainer_unavailable",
    fallbackMsg:
      "Create trainer route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
