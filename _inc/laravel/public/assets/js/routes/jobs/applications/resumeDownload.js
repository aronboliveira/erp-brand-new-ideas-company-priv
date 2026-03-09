/**
 * @file Resume Download Route Guard
 * @description Guards resume download links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard(
    'a[id^="resume-download-btn-"][data-url][data-guard-msg]',
    {
      msgKey: "resume_download_unavailable",
      fallbackMsg:
        "Resume download is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
