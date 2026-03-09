/**
 * @file Custom Field Edit Route Guard
 * @description Guards edit custom field links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard(".edit-custom-field-link[data-ajax-popup][data-url]");
  } catch {}
})();
