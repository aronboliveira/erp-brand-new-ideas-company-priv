/**
 * @file Estimation products forms route guard
 * @description Guards update and store forms using ERPGuard singleton
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard(
      "#est-prod-update-form, #est-prod-store-form",
      {
        msg: btoa(
          "Product route is unavailable. Please contact technical support or your domain administrator.",
        ),
      },
    );
  } catch (err) {}
})();
