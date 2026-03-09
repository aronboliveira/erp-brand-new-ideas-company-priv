(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard(
    'form[id^="loan-delete-form-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Delete loan route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
  guard.bindClickGuard('a[id^="loan-delete-link-"][data-url][data-guard-msg]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Delete loan route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
