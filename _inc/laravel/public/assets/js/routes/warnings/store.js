/**
 * @file Warning store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard(
      "form#create_warning[data-resolved-action][data-guard-msg]",
      {
        msg: btoa(
          "Store warning route is unavailable. Please contact technical support or your domain administrator.",
        ),
      },
    );
  } catch {}
})();
