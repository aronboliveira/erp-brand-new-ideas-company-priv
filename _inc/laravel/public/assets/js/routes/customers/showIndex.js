(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#customer-index-breadcrumb", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Customer index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
