/**
 * @fileoverview Employee update route guard
 * @description Protects employee update forms from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard(
      'form[id^="employee-update-form-"][data-url][data-guard-msg]',
      "VXBkYXRlIGVtcGxveWVlIHJvdXRlIGlzIHVuYXZhaWxhYmxlLiBQbGVhc2UgY29udGFjdCB0ZWNobmljYWwgc3VwcG9ydCBvciB5b3VyIGRvbWFpbiBhZG1pbmlzdHJhdG9yLg==",
    );
  } catch {}
})();
