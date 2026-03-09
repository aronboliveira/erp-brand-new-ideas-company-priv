(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#budget-planner-cancel-btn-half-yearly", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Cancel half-yearly budget route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
