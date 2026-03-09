(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard(
    'a[id^="delete-designation-btn-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Delete designation route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
