(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#product-stock-export", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Export product stock route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
