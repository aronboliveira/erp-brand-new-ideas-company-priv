/**
 * @file Journal Entries Generate Edit Route Guard
 * @description Guards AI generation links for journal entries using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard('[data-ajax-popup-over="true"][data-url]', {
      fallbackMsg: "AI generation route unavailable.",
    });
  } catch (_) {}
})();
