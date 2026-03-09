/**
 * @fileoverview Meeting generate update route guard
 * @description Protects AI generate meeting button from clicks when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard(
      "#ai-generate-meeting-btn",
      "R2VuZXJhdGUgcm91dGUgaXMgdW5hdmFpbGFibGUuIFBsZWFzZSBjb250YWN0IHRlY2huaWNhbCBzdXBwb3J0IG9yIHlvdXIgZG9tYWluIGFkbWluaXN0cmF0b3Iu",
    );
  } catch {}
})();
