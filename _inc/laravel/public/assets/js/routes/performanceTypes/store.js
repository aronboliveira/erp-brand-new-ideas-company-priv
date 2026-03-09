(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#pfm-tp-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Performance type route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
