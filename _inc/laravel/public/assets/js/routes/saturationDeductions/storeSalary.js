(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#saturation-deduction-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Store saturation deduction route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[id^="saturation-deduction-edit-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Edit saturation deduction route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[id^="saturation-deduction-delete-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Delete saturation deduction route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
