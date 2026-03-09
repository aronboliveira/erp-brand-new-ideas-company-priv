/**
 * @file Zoom Meetings Create Route Guard
 * @description Guards zoom meeting list/create links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    const fallbackMsg =
      "Requested route is unavailable. Please contact technical support or your domain administrator.";

    guard.bindClickGuard("#zoom-list-link", {
      fallbackMsg,
      validateUrl: true,
      updateHref: true,
    });

    guard.bindClickGuard("#zoom-create-link", {
      fallbackMsg,
      validateUrl: true,
      updateHref: true,
    });
  } catch (_) {}
})();
