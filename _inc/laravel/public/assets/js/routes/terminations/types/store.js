(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const formId = "termination-type-create-form";
    const f = document.getElementById(formId);
    if (!f || f.getAttribute("data-listener-active") === "true") {
      return;
    }
    f.setAttribute("data-listener-active", "true");

    f.addEventListener("submit", e => {
      try {
        const action = (f.getAttribute("action") ?? "").trim() || "#";
        const url = (f.getAttribute("data-url") ?? "").trim() || action || "#";
        if (action !== "#" || url !== "#") {
          return;
        }

        e.preventDefault();
        const msg =
          f.getAttribute("data-guard-msg") ||
          getMsg("create_termination_type_unavailable");
        scheduleError(msg, "submit");
        f.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (error) {}
})();
