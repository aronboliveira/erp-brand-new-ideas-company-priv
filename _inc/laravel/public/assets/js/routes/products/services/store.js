/**
 * @fileoverview Product/Service store route guard
 * @description Protects product/service store form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard(
      "#prd-sv-store-form",
      "UHJvZHVjdC9TZXJ2aWNlIHN0b3JlIHJvdXRlIGlzIHVuYXZhaWxhYmxlLiBQbGVhc2UgY29udGFjdCB0ZWNobmljYWwgc3VwcG9ydCBvciB5b3VyIGRvbWFpbiBhZG1pbmlzdHJhdG9yLg==",
    );
  } catch {}
})();
