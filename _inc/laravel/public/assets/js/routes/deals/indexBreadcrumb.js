/**
 * Deals Index Breadcrumb Route Guards
 * Handles deal index breadcrumb link
 * @module routes/deals/indexBreadcrumb
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#deal-index-breadcrumb", {
    fallbackMsg:
      "Deal index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
