/**
 * @fileoverview Invoice store route guard
 * @description Protects invoice store form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard(
      "#invoice-store-form",
      "U3RvcmUgaW52b2ljZSByb3V0ZSBpcyB1bmF2YWlsYWJsZS4gUGxlYXNlIGNvbnRhY3QgdGVjaG5pY2FsIHN1cHBvcnQgb3IgeW91ciBkb21haW4gYWRtaW5pc3RyYXRvci4=",
    );
  } catch {}
})();
