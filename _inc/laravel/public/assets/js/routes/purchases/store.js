(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#purchase-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Store purchase route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
