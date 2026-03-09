/**
 * @fileoverview Plan update route guard
 * @description Protects plan update form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard(
      "#plan-update-form",
      "VXBkYXRlIFBsYW4gcm91dGUgaXMgdW5hdmFpbGFibGUuIFBsZWFzZSBjb250YWN0IHRlY2huaWNhbCBzdXBwb3J0IG9yIHlvdXIgZG9tYWluIGFkbWluaXN0cmF0b3Iu",
    );
  } catch {}
})();
