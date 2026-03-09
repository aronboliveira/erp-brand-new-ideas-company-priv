(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const billTemplateSettingsForm = document.getElementById(
      "bill-template-settings-form",
    );
    if (!billTemplateSettingsForm) {
      return;
    }
    if (
      billTemplateSettingsForm.getAttribute("data-listener-active") === "true"
    ) {
      return;
    }
    billTemplateSettingsForm.setAttribute("data-listener-active", "true");

    billTemplateSettingsForm.addEventListener("submit", e => {
      try {
        const actionUrl =
          billTemplateSettingsForm.getAttribute("action") ?? "#";
        const dataUrl =
          billTemplateSettingsForm.getAttribute("data-url") ?? actionUrl ?? "#";
        if (dataUrl !== "#" && actionUrl !== "#") {
          return;
        }
        e.preventDefault();
        const guardMsg =
          billTemplateSettingsForm.getAttribute("data-guard-msg") ||
          getMsg("bill_settings_unavailable");
        scheduleError(guardMsg, "submit");
        billTemplateSettingsForm.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (err) {}
})();
