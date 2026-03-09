/**
 * @file Interview Schedules Show Route Guard
 * @description Guards modal links/forms and initializes tooltips using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }

    document.addEventListener("DOMContentLoaded", () => {
      guard.initTooltips();
      guard.bindClickGuard(
        ".modal-body a[data-guard-msg], .modal-body a[data-url]",
      );
      guard.bindSubmitGuard(
        ".modal-body form[data-guard-msg], .modal-body form[data-url]",
      );
    });
  } catch (err) {
    console.error("Error initializing interviewSchedules/show guard:", err);
  }
})();
