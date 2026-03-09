/**
 * @file Product Services Units Index Route Guard
 * @description Guards unit links and initializes tooltips using ERPGuard singleton with MutationObserver
 */
(function () {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }

    function init() {
      guard.bindClickGuard("a[data-guard-msg], a[data-url]");
      guard.initTooltips();
    }

    document.addEventListener("DOMContentLoaded", function () {
      init();
      const mo = new MutationObserver(init);
      mo.observe(document.body, { childList: true, subtree: true });
    });
  } catch (err) {
    console.error(
      "Error initializing products/services/units index guard:",
      err,
    );
  }
})();
