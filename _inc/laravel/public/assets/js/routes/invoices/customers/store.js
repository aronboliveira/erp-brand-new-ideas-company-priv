(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  const selectEl = document.getElementById("customer");
  if (!selectEl || selectEl.getAttribute("data-listener-active") === "true")
    return;
  selectEl.setAttribute("data-listener-active", "true");
  selectEl.addEventListener("change", event => {
    try {
      const url = selectEl.getAttribute("data-url");
      if (url && url !== "#") return;
      event.preventDefault();
      const msg =
        selectEl.getAttribute("data-guard-msg") ||
        getMsg("customer_select_unavailable");
      scheduleError(msg, "change");
      selectEl.setAttribute("data-failed-route", "true");
    } catch (e) {}
  });
})();
