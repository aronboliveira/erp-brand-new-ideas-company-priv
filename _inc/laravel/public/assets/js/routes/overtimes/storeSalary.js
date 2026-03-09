(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#overtime-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Store overtime route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[id^="overtime-edit-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Edit overtime route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard('[id^="overtime-delete-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Delete overtime route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
