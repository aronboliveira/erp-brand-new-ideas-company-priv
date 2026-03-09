/**
 * @file Employee Home Route Guard
 * @description Guards the breadcrumb home link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#bc-home-link", {
    msgKey: "home_route_unavailable",
    fallbackMsg:
      "Home route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
