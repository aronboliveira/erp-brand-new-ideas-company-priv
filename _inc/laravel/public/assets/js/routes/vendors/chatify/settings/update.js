/**
 * @fileoverview Chatify settings update route guard
 * @description Protects chatify avatar update form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard(
      "#update-settings",
      "VXBkYXRlIGF2YXRhciByb3V0ZSBpcyB1bmF2YWlsYWJsZS4gUGxlYXNlIGNvbnRhY3QgdGVjaG5pY2FsIHN1cHBvcnQgb3IgeW91ciBkb21haW4gYWRtaW5pc3RyYXRvci4=",
    );
  } catch {}
})();
