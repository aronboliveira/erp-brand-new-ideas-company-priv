/**
 * @file Products/Services Generate Edit Route Guard
 * @description Guards generate content button for product/service using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard(
      'a[data-url][data-guard-msg][data-sv-localized="true"].btn-icon',
      {
        fallbackMsg:
          "Generate content route for Product/Service is unavailable. Please contact technical support or your domain administrator.",
      },
    );
  } catch (_) {}
})();
