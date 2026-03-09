(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#createCompetencyBtn", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Requested route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[data-listener-alias="edit-competency"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Requested route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[data-listener-alias="delete-competency"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Requested route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
