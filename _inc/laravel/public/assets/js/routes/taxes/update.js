/**
 * @file Tax update form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#update-tax-form", {
      msg: btoa(
        "Update tax route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (error) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error(
        "[assets/js/routes/taxes/update.js] Initialization error:",
        error?.constructor?.name ?? "Error",
        error?.message ?? "Unknown error",
      );
  }
})();
