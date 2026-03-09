(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard(
    'a[id^="saturation-deduction-edit-link-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Edit saturation deduction route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
