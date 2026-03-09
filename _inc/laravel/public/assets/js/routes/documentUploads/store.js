(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#document-upload-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Store document route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
