/**
 * @file Zoom Meeting Store Route Guard
 * @description Guards the zoom meeting creation form using ERPGuard singleton
 * @requires ERPGuard
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#store_zoom_meeting", {
    msgKey: "store_zoom_meeting_unavailable",
    fallbackMsg:
      "Store zoom meeting route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
