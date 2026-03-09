/**
 * @file Customer Transaction Reset Route Guard
 * @description Guards the transaction reset button using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#transaction-reset-btn", {
    msgKey: "reset_transaction_unavailable",
    fallbackMsg:
      "Reset transaction route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
