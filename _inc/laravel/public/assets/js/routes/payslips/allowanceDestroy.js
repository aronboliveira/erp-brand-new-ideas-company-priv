(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard(
    'form[id^="allowance-delete-form-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Delete allowance route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
  guard.bindClickGuard(
    'a[id^="allowance-delete-link-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Delete allowance route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
