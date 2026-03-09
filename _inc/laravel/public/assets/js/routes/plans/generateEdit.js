/**
 * @fileoverview Plan generate edit route guard
 * @description Protects plan generate button from action when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard(
      "#generate-plan-btn",
      "R2VuZXJhdGUgY29udGVudCByb3V0ZSBmb3IgUGxhbiBpcyB1bmF2YWlsYWJsZS4gUGxlYXNlIGNvbnRhY3QgdGVjaG5pY2FsIHN1cHBvcnQgb3IgeW91ciBkb21haW4gYWRtaW5pc3RyYXRvci4=",
    );
  } catch {}
})();
