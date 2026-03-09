/**
 * @file Custom Field Store Route Guard
 * @description Guards the custom field creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#custom-field-store-form", {
    msgKey: "store_custom_field_unavailable",
    fallbackMsg:
      "Store custom field route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
