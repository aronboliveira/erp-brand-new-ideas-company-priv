(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.edit-purchase", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Edit purchase route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
