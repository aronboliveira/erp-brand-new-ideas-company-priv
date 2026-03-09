/**
 * @file Vendor Update Route Guard
 * @description Guards the vendor update form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#vendor-update-form", {
    msgKey: "update_vendor_unavailable",
    fallbackMsg:
      "Update vendor route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
