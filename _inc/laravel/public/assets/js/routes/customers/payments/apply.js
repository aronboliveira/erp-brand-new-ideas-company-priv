/**
 * @file Customer Payment Filter Apply Route Guard
 * @description Guards the filter apply button using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#filter-apply-btn", {
    msgKey: "apply_filter_unavailable",
    fallbackMsg:
      "Apply filter route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
