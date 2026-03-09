/**
 * @file Jobs Edit Route Guard
 * @description Guards job update forms and AI/grammar buttons using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindSubmitGuard(
      'form[id^="job-update-form-"][data-url][data-guard-msg]',
      {
        fallbackMsg:
          "Job update route is unavailable. Please contact technical support or your domain administrator.",
      },
    );

    guard.bindClickGuard(
      "a.ai-btn[data-ajax-popup-over][data-url][data-guard-msg], a.grammar-btn[data-ajax-popup-over][data-url][data-guard-msg]",
    );
  } catch {}
})();
