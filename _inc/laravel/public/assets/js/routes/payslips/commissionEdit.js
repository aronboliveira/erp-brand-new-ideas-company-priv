(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard(
    'a[id^="commission-edit-link-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Edit commission route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
