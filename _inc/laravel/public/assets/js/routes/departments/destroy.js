(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard(
    'a[id^="delete-department-btn-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Delete department route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
