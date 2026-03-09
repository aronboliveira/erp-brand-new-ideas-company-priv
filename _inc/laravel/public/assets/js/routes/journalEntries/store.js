/**
 * @file Journal entry store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#jrn-et-store-form", {
      msg: btoa(
        "Journal entry store route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
