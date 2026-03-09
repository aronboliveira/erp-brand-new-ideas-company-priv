/**
 * Deal List Route Guards
 * Handles deal list button validation
 * @module routes/deals/list
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#deal-list-btn", {
    fallbackMsg: "# ERROR",
  });
})();
