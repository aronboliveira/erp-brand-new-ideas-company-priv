/**
 * @file Warehouse Update Route Guard
 * @description Guards the warehouse update form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard(
    "form#edit_warehouse[data-resolved-action][data-guard-msg]",
    {
      msgKey: "update_warehouse_unavailable",
      fallbackMsg:
        "Update warehouse route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
