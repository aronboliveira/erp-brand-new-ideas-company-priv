(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#mt-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Meeting store route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
