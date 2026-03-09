/**
 * @fileoverview Form submission guard for order status change using ERPGuard singleton
 * @module assets/js/routes/orders/changeStatus
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#order-change-status-form", {
      msg: btoa(
        "Change status route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
