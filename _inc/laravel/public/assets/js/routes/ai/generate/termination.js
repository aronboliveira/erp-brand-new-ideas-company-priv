(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const l = document.getElementById("termination-generate-link");
    if (!l || l.getAttribute("data-listener-active") === "true") return;
    l.setAttribute("data-listener-active", "true");
    l.addEventListener("click", e => {
      try {
        const href = l.getAttribute("href") || "#";
        const url = l.getAttribute("data-url") || href || "#";
        if (href !== "#" || url !== "#") return;
        e.preventDefault();
        const msg =
          l.getAttribute("data-guard-msg") ||
          getMsg("generate_termination_unavailable");
        scheduleError(msg, "click");
        l.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (error) {}
})();
