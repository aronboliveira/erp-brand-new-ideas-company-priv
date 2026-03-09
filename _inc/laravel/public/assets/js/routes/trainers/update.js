(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#update_trainer_form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Update trainer route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
