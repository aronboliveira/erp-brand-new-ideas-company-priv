(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#contracts-index-list-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Contracts index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
