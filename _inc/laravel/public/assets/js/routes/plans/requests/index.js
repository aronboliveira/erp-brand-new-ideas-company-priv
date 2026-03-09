/**
 * @file Plans Requests Index Route Guard
 * @description Guards plan request links/forms and initializes tooltips using ERPGuard singleton with MutationObserver
 */
(function () {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }

    function init() {
      guard.bindClickGuard("a[data-guard-msg], a[data-url]");
      guard.bindSubmitGuard("form[data-guard-msg], form[data-url]");
      guard.initTooltips();
    }

    document.addEventListener("DOMContentLoaded", function () {
      init();
      const mo = new MutationObserver(function () {
        init();
      });
      mo.observe(document.body, { childList: true, subtree: true });
    });
  } catch (err) {
    console.error("Error initializing plans/requests index guard:", err);
  }
})();
