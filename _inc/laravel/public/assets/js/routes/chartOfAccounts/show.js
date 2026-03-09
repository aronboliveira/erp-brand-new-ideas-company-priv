(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#report_drilldown", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Requested route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindClickGuard("#applyDrilldown", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Requested route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
