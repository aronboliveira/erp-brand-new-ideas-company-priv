(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#budget-planner-cancel-btn-monthly", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Cancel monthly budget route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
