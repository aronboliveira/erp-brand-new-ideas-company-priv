(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard('a[id^="loan-edit-link-"][data-url][data-guard-msg]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Edit loan route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
