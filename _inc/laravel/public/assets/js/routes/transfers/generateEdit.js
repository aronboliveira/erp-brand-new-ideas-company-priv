(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard(
    'a[data-ajax-popup-over="true"][data-url][data-guard-msg]',
    {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Generate transfer content route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
