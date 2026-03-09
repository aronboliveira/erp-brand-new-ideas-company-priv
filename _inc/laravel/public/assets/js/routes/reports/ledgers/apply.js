/**
 * Ledger Report Apply Route Guards
 * Handles ledger report apply button with form submission
 * @module routes/reports/ledgers/apply
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.apply-ledger", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    fallbackMsg:
      "Ledger report route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
