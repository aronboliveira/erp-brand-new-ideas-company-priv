(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#settings-pusher-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Pusher settings route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
