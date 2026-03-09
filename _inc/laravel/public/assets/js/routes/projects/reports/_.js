(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#{{ $projectReportExportLinkId }}", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Project report export route is unavailable. Please contact technical support or your domain administrator.",
  });
})();

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#{{ $projectTaskShowLinkId }}", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Project task show route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
