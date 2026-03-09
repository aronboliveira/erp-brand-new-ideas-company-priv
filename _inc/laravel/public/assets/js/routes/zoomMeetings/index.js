/**
 * @file Zoom Meetings Index Route Guard
 * @description Guards zoom calendar and create links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }
    guard.bindClickGuard("#zoom-calendar-link", {
      fallbackMsg:
        "Calendar route is unavailable. Please contact technical support or your domain administrator.",
    });
    guard.bindClickGuard("#zoom-create-link", {
      fallbackMsg:
        "Create zoom meeting route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (err) {
    console.error("Error initializing zoom meetings index guard:", err);
  }
})();
