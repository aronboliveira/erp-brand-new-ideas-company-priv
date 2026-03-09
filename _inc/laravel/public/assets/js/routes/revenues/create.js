(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.revenue-create", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create revenue route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
