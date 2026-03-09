(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const f = document.getElementById("store_client");
    if (!f) return;

    const guardMsg =
      f.getAttribute("data-guard-msg") || getMsg("route_unavailable");
    const actionHref = f.getAttribute("data-action-href") || "";

    if (!f.getAttribute("action") && actionHref && actionHref !== "#") {
      f.setAttribute("action", actionHref);
    }

    f.addEventListener("submit", e => {
      try {
        const a = f.getAttribute("action") || actionHref || "";
        if (!a || a === "#") {
          e.preventDefault();
          scheduleError(guardMsg, "submit");
        }
      } catch (_) {
        e.preventDefault();
        scheduleError(guardMsg, "submit");
      }
    });
  } catch (_) {}
})();
