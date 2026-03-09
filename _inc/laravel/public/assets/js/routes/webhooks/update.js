(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const f = document.querySelector(
      "form#edit_webhook[data-resolved-action][data-guard-msg]",
    );
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") || "#";
    if (
      (f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
      resolved !== "#"
    ) {
      f.setAttribute("action", resolved);
    }

    f.addEventListener("submit", e => {
      try {
        const action = f.getAttribute("action") || "#";
        if (action && action !== "#") return;
        e.preventDefault();

        const msg =
          f.getAttribute("data-guard-msg") ||
          getMsg("update_webhook_unavailable");
        scheduleError(msg, "submit");
        f.setAttribute("data-failed-route", "true");
      } catch (_) {}
    });
  } catch (_) {}
})();
