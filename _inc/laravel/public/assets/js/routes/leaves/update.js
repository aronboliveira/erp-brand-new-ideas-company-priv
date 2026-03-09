/**
 * @file Leave Update Route Guard
 * @description Guards leave update form using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindSubmitGuard("#edit_leave", {
      fallbackMsg:
        "Update leave route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (error) {}
})();
