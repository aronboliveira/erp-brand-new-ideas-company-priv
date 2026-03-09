/**
 * @file partials/admin/menu/coupon.js
 * @description Coupon index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#coupon-index-link");
  } catch (err) {
    console.error("Error initializing coupon index menu guard:", err);
  }
})();
