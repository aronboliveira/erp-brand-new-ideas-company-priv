(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#award-create-button", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create award route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[id^="award-edit-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Edit award route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[id^="award-delete-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Delete award route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
