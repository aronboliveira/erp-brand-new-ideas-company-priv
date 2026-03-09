/**
 * Purchases Index Route Guards
 * Handles purchase index breadcrumb link validation
 * @module routes/purchases/index
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#purchase-index-breadcrumb-link", {
    fallbackMsg:
      "Purchase index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
