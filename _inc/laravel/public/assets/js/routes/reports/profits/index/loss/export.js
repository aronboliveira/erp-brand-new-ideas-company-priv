(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#profit-loss-export", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Export profit and loss route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
