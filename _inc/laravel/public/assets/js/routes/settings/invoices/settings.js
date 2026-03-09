(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const formEl = document.getElementById("invoice-template-settings-form");
    if (!formEl) {
      return;
    }
    if (formEl.getAttribute("data-listener-active") === "true") {
      return;
    }
    formEl.setAttribute("data-listener-active", "true");
    formEl.addEventListener("submit", e => {
      try {
        const actionUrl = formEl.getAttribute("action") ?? "#";
        const dataUrl = formEl.getAttribute("data-url") ?? actionUrl ?? "#";
        if (dataUrl !== "#" && actionUrl !== "#") {
          return;
        }
        e.preventDefault();
        const guardMsg =
          formEl.getAttribute("data-guard-msg") ||
          getMsg("invoice_settings_unavailable");
        scheduleError(guardMsg, "submit");
        formEl.setAttribute("data-failed-route", "true");
      } catch (_) {}
    });
  } catch (_) {}
})();
