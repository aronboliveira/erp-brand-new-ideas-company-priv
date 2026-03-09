(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#pills-profile-tab", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Monthly purchase route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
