/**
 * @fileoverview Permission update route guard
 * @description Protects permission update form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard(
      "#permission-update-form",
      "VXBkYXRlIFBlcm1pc3Npb24gcm91dGUgaXMgdW5hdmFpbGFibGUuIFBsZWFzZSBjb250YWN0IHRlY2huaWNhbCBzdXBwb3J0IG9yIHlvdXIgZG9tYWluIGFkbWluaXN0cmF0b3Iu",
    );
  } catch {}
})();
