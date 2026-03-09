/**
 * @file Employee profile filter form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#employee_profile_filter", {
      msg: btoa(
        "Profile employee route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
