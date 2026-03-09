(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const l = document.getElementById("payslip-link");
    if (!l) {
      return;
    }
    if (l.getAttribute("data-listener-active") === "true") {
      return;
    }
    l.setAttribute("data-listener-active", "true");

    l.addEventListener("click", e => {
      try {
        const href = (l.getAttribute("href") ?? "#").trim();
        const url = (l.getAttribute("data-url") ?? href ?? "#").trim();
        if (url !== "#" && href !== "#") {
          return;
        }

        e.preventDefault();

        const msg =
          l.getAttribute("data-guard-msg") ||
          getMsg("payslip_route_unavailable");
        scheduleError(msg, "click");
        l.setAttribute("data-failed-route", "true");
      } catch (_) {}
    });
  } catch (_) {}
})();
