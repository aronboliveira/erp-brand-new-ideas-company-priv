(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard('[data-listener-alias="view-estimate"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Requested route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[data-listener-alias="edit-estimate"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Requested route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[data-listener-alias="delete-estimate"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Requested route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
