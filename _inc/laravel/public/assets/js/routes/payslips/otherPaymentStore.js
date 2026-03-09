/**
 * @file Other Payment Store Route Guard
 * @description Guards other payment store forms using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindSubmitGuard(
      'form[id^="other-payment-store-form-"][data-url][data-guard-msg]',
      {
        fallbackMsg:
          "Store other payment route is unavailable. Please contact technical support or your domain administrator.",
      },
    );
  } catch (err) {}
})();
