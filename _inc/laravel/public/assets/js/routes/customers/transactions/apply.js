/**
 * @file Customer Transaction Apply Route Guard
 * @description Guards the transaction apply button using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#transaction-apply-btn", {
    msgKey: "apply_transaction_unavailable",
    fallbackMsg:
      "Apply transaction route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
