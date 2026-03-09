(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard(
    'a[id^="employee-show-btn-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Show employee route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
