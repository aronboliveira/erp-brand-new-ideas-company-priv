(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#pos-print-btn", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Print POS barcode route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
