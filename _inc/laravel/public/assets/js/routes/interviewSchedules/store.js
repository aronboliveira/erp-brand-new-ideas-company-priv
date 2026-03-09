/**
 * @fileoverview Interview schedule store route guard
 * @description Protects interview schedule store form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard(
      "#iv-sch-store-form",
      "SW50ZXJ2aWV3IHNjaGVkdWxlIHN0b3JlIHJvdXRlIGlzIHVuYXZhaWxhYmxlLiBQbGVhc2UgY29udGFjdCB0ZWNobmljYWwgc3VwcG9ydCBvciB5b3VyIGRvbWFpbiBhZG1pbmlzdHJhdG9yLg==",
    );
  } catch {}
})();
