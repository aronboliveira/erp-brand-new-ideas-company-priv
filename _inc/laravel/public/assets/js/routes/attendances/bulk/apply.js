/**
 * @file Attendances Bulk Apply Route Guard
 * @description Guards bulk attendance apply links with form submission using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.apply-bulkattendance", {
      fallbackMsg:
        "Apply bulk attendance route is unavailable. Please contact technical support or your domain administrator.",
      handler(event, element) {
        event.preventDefault();
        const formId = element.getAttribute("data-form-id") ?? "";
        if (!formId) return;
        const form = document.getElementById(formId);
        if (!form) return;

        const action = form.getAttribute("action") ?? "#";
        if (action !== "#") {
          form.submit();
          return;
        }

        const msg =
          element.getAttribute("data-guard-msg") ??
          "Apply bulk attendance route is unavailable. Please contact technical support or your domain administrator.";
        guard.showToast(msg);
        element.setAttribute("data-failed-route", "true");
        form.setAttribute("data-failed-route", "true");
      },
    });
  } catch (_) {}
})();
