(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard(
    'form[id^="commission-delete-form-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Delete commission route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
  guard.bindClickGuard(
    'a[id^="commission-delete-link-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Delete commission route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
