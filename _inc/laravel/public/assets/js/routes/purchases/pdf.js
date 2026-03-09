/**
 * Purchase PDF Route Guards
 * Handles purchase PDF link validation
 * @module routes/purchases/pdf
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.purchase-pdf", {
    fallbackMsg:
      "PDF purchase route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
