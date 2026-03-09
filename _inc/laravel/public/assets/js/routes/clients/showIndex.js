/**
 * Clients Show Index Route Guards
 * Handles client index breadcrumb link
 * @module routes/clients/showIndex
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#breadcrumb-client-index-link", {
    fallbackMsg:
      "Client index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
