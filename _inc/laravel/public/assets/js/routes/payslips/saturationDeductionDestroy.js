(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard(
    'form[id^="saturation-deduction-delete-form-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Delete saturation deduction route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
  guard.bindClickGuard(
    'a[id^="saturation-deduction-delete-link-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Delete saturation deduction route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
