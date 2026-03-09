(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard(
    'form[id^="loan-store-form-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Store loan route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
