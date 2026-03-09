/**
 * @file Reports Invoice Summary Apply Route Guard
 * @description Guards invoice summary apply links with form submission using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.apply-invoice-summary", {
      fallbackMsg:
        "Invoice summary report route is unavailable. Please contact technical support or your domain administrator.",
      handler(event, element) {
        event.preventDefault();
        const formId = element.getAttribute("data-form-id") || "";
        const form = formId ? document.getElementById(formId) : null;
        if (!form) return;

        const action = form.getAttribute("action") || "#";
        const url = form.getAttribute("data-url") || action || "#";
        if (url !== "#") {
          try {
            form.submit();
          } catch (_) {}
          return;
        }

        const msg =
          form.getAttribute("data-guard-msg") ||
          element.getAttribute("data-guard-msg") ||
          "Invoice summary report route is unavailable. Please contact technical support or your domain administrator.";
        guard.showToast(msg);
        form.setAttribute("data-failed-route", "true");
      },
    });
  } catch (_) {}
})();
