(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#training-index-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Training index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
