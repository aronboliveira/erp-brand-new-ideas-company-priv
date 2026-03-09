/**
 * @file Saturation Deduction Store Route Guard
 * @description Guards saturation deduction store forms using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindSubmitGuard(
      'form[id^="saturation-deduction-store-form-"][data-url][data-guard-msg]',
      {
        fallbackMsg:
          "Store saturation deduction route is unavailable. Please contact technical support or your domain administrator.",
      },
    );
  } catch (err) {}
})();
