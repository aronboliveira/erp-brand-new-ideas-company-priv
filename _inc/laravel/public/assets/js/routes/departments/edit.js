(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard(
    'a[id^="department-edit-btn-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Edit department route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
