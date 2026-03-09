(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard('form[id^="email-template-update-form-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Update email template route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
