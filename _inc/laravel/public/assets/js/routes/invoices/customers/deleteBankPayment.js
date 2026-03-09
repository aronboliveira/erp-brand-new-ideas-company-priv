(() => {
  if (typeof window !== "undefined" && window.ERPGuard) {
    const guard = window.ERPGuard;
    guard.bindClickGuard('[data-listener-alias^="delete-bankpayment-"]', {
      fallbackMsg: "# ERROR",
      validateUrl: true,
    });
  }
})();
