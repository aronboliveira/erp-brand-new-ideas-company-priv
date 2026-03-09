(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#pos-product-barcode-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Access pos product barcode route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
