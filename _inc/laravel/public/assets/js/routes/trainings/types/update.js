(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#update_training_type_form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Update training type route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
