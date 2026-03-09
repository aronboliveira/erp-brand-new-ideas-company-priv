(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard('form[id^="edit-saturation-deduction-form-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Update saturation deduction route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
