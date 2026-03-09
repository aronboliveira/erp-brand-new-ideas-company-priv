(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#store_training_type_form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Store training type route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
