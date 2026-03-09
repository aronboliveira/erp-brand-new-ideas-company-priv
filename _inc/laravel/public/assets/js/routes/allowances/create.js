/**
 * @file Allowance Create Route Guard
 * @description Guards allowance creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#allowance-store-form", {
    msgKey: "create_allowance_unavailable",
    fallbackMsg: "# ERROR",
  });
})();
