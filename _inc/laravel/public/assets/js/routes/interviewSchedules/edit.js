/**
 * @file Interview schedule edit form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#interviewSchedule-edit-form", {
      msg: btoa(
        "Update interview schedule route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
