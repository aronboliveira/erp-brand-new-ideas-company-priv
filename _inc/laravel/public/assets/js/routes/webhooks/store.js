(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard(
    "form#create_webhook[data-resolved-action][data-guard-msg]",
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Store webhook route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
