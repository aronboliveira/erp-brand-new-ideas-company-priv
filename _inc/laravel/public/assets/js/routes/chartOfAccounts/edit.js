/**
 * @file Chart of Accounts Edit Route Guard
 * @description Guards bill summary filter, apply button, and account management links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindSubmitGuard("#report_bill_summary");
    guard.bindClickGuard("#applyFilter");
    guard.bindClickGuard(
      '[data-listener-alias="ledger-link"], [data-listener-alias="edit-account"], [data-listener-alias="delete-account"]',
    );
  } catch {}
})();
