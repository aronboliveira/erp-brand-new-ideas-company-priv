/**
 * @fileoverview Document upload update route guard
 * @description Protects document upload update forms from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard(
      'form[id^="document-upload-update-form-"][data-url][data-guard-msg]',
      "VXBkYXRlIGRvY3VtZW50IHJvdXRlIGlzIHVuYXZhaWxhYmxlLiBQbGVhc2UgY29udGFjdCB0ZWNobmljYWwgc3VwcG9ydCBvciB5b3VyIGRvbWFpbiBhZG1pbmlzdHJhdG9yLg==",
    );
  } catch {}
})();
