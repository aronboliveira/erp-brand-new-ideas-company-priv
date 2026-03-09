(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.purchase-show", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Show purchase route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
