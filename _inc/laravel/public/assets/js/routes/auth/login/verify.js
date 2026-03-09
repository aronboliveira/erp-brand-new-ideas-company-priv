(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  const guardForm = formId => {
    const form = document.getElementById(formId);
    if (!form || form.getAttribute("data-listener-active") === "true") return;
    form.setAttribute("data-listener-active", "true");
    form.addEventListener("submit", event => {
      try {
        const action = form.getAttribute("action");
        const url = form.getAttribute("data-url");
        if ((action && action !== "#") || (url && url !== "#")) return;
        event.preventDefault();
        const msg =
          form.getAttribute("data-guard-msg") ||
          getMsg("verify_form_unavailable");
        scheduleError(msg, "submit");
        form.setAttribute("data-failed-route", "true");
      } catch (e) {}
    });
  };
  guardForm("resend-verification-form");
  guardForm("logout-form");
})();
