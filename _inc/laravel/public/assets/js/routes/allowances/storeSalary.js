(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#allowance-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Store allowance route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[id^="allowance-edit-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Edit allowance route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[id^="allowance-delete-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Delete allowance route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
