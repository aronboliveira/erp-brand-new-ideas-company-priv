(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const a = document.getElementById("userlog-reset-link");
    if (!a || a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");
    const url = a.getAttribute("data-url") ?? "#";
    if (
      (a.getAttribute("href") === "#" || !a.getAttribute("href")) &&
      url !== "#"
    ) {
      a.setAttribute("href", url);
    }
    a.addEventListener("click", e => {
      const href = a.getAttribute("href") ?? "#";
      if (href && href !== "#") return;
      e.preventDefault();
      const msg =
        a.getAttribute("data-guard-msg") || getMsg("user_logs_unavailable");
      scheduleError(msg, "click");
      a.setAttribute("data-failed-route", "true");
    });
  } catch {}
})();
