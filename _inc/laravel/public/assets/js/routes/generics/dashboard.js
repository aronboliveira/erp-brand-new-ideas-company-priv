/**
 * @file Generic Dashboard Route Guard
 * @description Guards the dashboard breadcrumb link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#dashboard-breadcrumb-link", {
    msgKey: "dashboard_unavailable",
    fallbackMsg:
      "Dashboard route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
