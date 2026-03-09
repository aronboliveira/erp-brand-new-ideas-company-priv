(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard(
    'form[id^="overtime-store-form-"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Store overtime route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
