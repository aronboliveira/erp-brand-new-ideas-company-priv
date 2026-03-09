/**
 * @file Generic Utility - Display Unavailable Route Message
 * @description Wrapper function for ERPGuard toast display (backwards compatibility)
 * @deprecated Use window.ERPGuard.showToast() directly instead
 */

/**
 * Display unavailable route message using ERPGuard singleton
 * @param {string} [lang='pt-br'] - Language code (deprecated, now auto-detected)
 * @param {string} [msg=null] - Custom message
 * @returns {void}
 */
function displayUnavailableRouteMessage(lang = "pt-br", msg = null) {
  const guard = window.ERPGuard;
  if (!guard) {
    alert(msg || "Route unavailable!");
    return;
  }

  const message =
    msg || guard.getMsg(null, "route_unavailable") || "Route unavailable!";
  guard.showToast(message, "error");
}
