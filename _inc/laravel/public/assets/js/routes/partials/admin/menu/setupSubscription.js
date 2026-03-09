/**
 * @file partials/admin/menu/setupSubscription.js
 * @description Setup subscription plan menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#setup-subscription-plan-link");
  } catch (err) {
    console.error("Error initializing setup subscription menu guard:", err);
  }
})();
