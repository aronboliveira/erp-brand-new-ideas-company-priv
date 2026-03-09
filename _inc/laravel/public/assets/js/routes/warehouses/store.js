/**
 * @file Warehouse Store Route Guard
 * @description Guards warehouse forms using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('form[data-resolved-action][data-guard-msg]', {
    msgKey: 'store_warehouse_unavailable',
    fallbackMsg: 'Store warehouse route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
