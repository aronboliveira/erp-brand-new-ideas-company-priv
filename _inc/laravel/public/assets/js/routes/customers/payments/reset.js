/**
 * @file Customer Payment Filter Reset Route Guard
 * @description Guards the filter reset button using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#filter-reset-btn", {
    msgKey: "reset_filter_unavailable",
    fallbackMsg:
      "Reset filter route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
