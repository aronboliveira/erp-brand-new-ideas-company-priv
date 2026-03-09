(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#warehouse-transfer-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Store warehouse transfer route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
