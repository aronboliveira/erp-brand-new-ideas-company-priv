/**
 * @fileoverview Employee show training route guard
 * @description Protects employee show link from navigation when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard(
      "#employee-show-link",
      "U2hvdyBlbXBsb3llZSByb3V0ZSBpcyB1bmF2YWlsYWJsZS4gUGxlYXNlIGNvbnRhY3QgdGVjaG5pY2FsIHN1cHBvcnQgb3IgeW91ciBkb21haW4gYWRtaW5pc3RyYXRvci4=",
    );
  } catch {}
})();
