/**
 * @file Leave Type Edit Route Guard
 * @description Guards edit leave type links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard(".edit-leavetype-link[data-url]");
  } catch {}
})();
