/**
 * @file partials/admin/menu/companyPolicy.js
 * @description Company policy menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#company-policy-link");
  } catch (err) {
    console.error("Error initializing company policy menu guard:", err);
  }
})();
