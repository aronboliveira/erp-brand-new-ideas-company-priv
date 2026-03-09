(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard(
    "form#edit_warning[data-resolved-action][data-guard-msg]",
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Update warning route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
