(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#lbl-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Label store route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
