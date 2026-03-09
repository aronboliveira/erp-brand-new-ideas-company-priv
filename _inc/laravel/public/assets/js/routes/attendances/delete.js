/**
 * @file Attendance Delete Route Guard
 * @description Guards attendance delete links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard('[id^="delete-attendance-link-"]', {
      fallbackMsg:
        "Delete attendance route is unavailable. Please contact technical support or your domain administrator.",
      validateUrl: true,
    });
  } catch (_) {}
})();
