/**
 * @file Journal entry edit form and cancel button route guards
 * @description Prevents submission/navigation if routes are unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#journalEntry-edit-form", {
      msg: btoa(
        "Journal entry edit route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
    window.ERPGuard.bindClickGuard('.modal-footer [value="Cancel"]', {
      msg: btoa(
        "Journal entries index route is unavailable. Please contact technical support or your domain administrator.",
      ),
      hrefAttr: "data-index-url",
    });
  } catch {}
})();
