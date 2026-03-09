/**
 * @fileoverview Invoice customer PDF route guard
 * @description Protects invoice PDF download link from clicks when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard(
      '[data-listener-alias="download-invoice-pdf"]',
      "IyBFUlJPUg==",
    );
  } catch {}
})();
