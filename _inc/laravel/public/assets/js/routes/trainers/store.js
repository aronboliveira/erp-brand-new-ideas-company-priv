/**
 * @file Trainer store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#store_trainer_form", {
      msg: btoa(
        "Store trainer route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (error) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error(
        "[assets/js/routes/trainers/store.js] Initialization error:",
        error?.constructor?.name ?? "Error",
        error?.message ?? "Unknown error",
      );
  }
})();
