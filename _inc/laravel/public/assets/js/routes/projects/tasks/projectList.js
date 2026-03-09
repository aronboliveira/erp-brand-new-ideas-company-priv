(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard('a[id^="task-index-link-"]', {
    msgKey: "action_unavailable",
    fallbackMsg:
      "View project tasks route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
