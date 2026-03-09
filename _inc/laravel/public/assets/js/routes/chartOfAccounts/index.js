/**
 * Chart of Accounts Index Route Guards
 * Handles chart of accounts index breadcrumb link
 * @module routes/chartOfAccounts/index
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#breadcrumb-coa-index-link", {
    fallbackMsg:
      "Chart of account index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
