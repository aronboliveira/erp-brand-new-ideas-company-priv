(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard(
    'form[id^="overtime-delete-form-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Delete overtime route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
  guard.bindClickGuard(
    'a[id^="overtime-delete-link-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Delete overtime route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
