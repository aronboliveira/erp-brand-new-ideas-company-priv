/**
 * @file partials/admin/menu/info.js
 * @description HR info menu link guards (awards, transfers, etc.) using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    [
      "#award-index-link",
      "#transfer-index-link",
      "#resignation-index-link",
      "#trip-index-link",
      "#promotion-index-link",
      "#complaint-index-link",
      "#warning-index-link",
      "#termination-index-link",
      "#announcement-index-link",
      "#holidays-index-link",
    ].forEach(id => window.ERPGuard.bindClickGuard(id));
  } catch (err) {
    console.error("Error initializing HR info menu guards:", err);
  }
})();
