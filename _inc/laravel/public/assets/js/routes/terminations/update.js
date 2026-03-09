/**
 * @file Termination update form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#edit_termination", {
      msg: btoa(
        "Update termination route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (error) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error(
        "[assets/js/routes/terminations/update.js] Initialization error:",
        error?.constructor?.name ?? "Error",
        error?.message ?? "Unknown error",
      );
  }
})();
