/**
 * @file Client Update Route Guard
 * @description Guards client update form using ERPGuard singleton
 */
(function () {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    const f = document.getElementById("edit_client");
    if (!f) return;

    guard.bindSubmitGuard("#edit_client", {
      fallbackMsg: "Route unavailable",
    });
  } catch (_) {}
})();
