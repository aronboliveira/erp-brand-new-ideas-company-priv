/**
 * @fileoverview Lead stage update route guard
 * @description Protects lead stage edit form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard(
      "#leaveType-edit-form",
      "VXBkYXRlIHJvdXRlIGlzIHVuYXZhaWxhYmxlLiBQbGVhc2UgY29udGFjdCB0ZWNobmljYWwgc3VwcG9ydCBvciB5b3VyIGRvbWFpbiBhZG1pbmlzdHJhdG9yLg==",
    );
  } catch {}
})();
