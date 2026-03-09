/**
 * @fileoverview Plan store route guard
 * @description Protects plan store form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard(
      "#pln-store-form",
      "UGxhbiBzdG9yZSByb3V0ZSBpcyB1bmF2YWlsYWJsZS4gUGxlYXNlIGNvbnRhY3QgdGVjaG5pY2FsIHN1cHBvcnQgb3IgeW91ciBkb21haW4gYWRtaW5pc3RyYXRvci4=",
    );
  } catch {}
})();
