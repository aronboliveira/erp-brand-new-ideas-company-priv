(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#budget-planner-index-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Budget planner index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
