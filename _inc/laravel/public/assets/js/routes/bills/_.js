/**
 * Bills Delete Payment Route Guards
 * Handles payment deletion button
 * @module routes/bills/_
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#{{ $deletePaymentBtnId }}", {
    fallbackMsg:
      "Bill payment deletion route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
