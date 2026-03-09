/**
 * @file Complaint Store Route Guard
 * @description Guards the complaint creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#complaintStoreForm", {
    msgKey: "store_complaint_unavailable",
    fallbackMsg:
      "Create complaint route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
