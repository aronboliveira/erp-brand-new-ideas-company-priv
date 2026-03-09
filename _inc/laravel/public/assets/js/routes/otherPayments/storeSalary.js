(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#other-payment-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Store other payment route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[id^="other-payment-edit-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Edit other payment route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[id^="other-payment-delete-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Delete other payment route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
