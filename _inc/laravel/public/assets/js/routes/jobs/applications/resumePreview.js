/**
 * @file Resume Preview Route Guard
 * @description Guards resume preview links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard(
    'a[id^="resume-preview-btn-"][data-url][data-guard-msg]',
    {
      msgKey: "resume_preview_unavailable",
      fallbackMsg:
        "Resume preview is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
