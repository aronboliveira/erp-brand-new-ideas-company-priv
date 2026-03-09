(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  try {
    const f = document.getElementById("message-form");
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
      const action = f.getAttribute("action") || "#";
      if (action && action !== "#") return;
      e.preventDefault();
      const msg =
        f.getAttribute("data-guard-msg") || getMsg("send_message_unavailable");
      scheduleError(msg, "submit");
      f.setAttribute("data-failed-route", "true");
    });
  } catch {}
})();
