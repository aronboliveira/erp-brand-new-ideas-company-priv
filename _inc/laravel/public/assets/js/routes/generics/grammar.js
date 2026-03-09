(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const l = document.getElementById("grammarCheck");
    if (!l) return;
    const flag = "data-click-listener";
    if (l.hasAttribute(flag) && l.getAttribute(flag) === "true") return;
    l.setAttribute(flag, "true");
    l.addEventListener(
      "click",
      function (e) {
        try {
          const href = l.getAttribute("href") || "#";
          const url = l.getAttribute("data-url") || href || "#";
          if (href !== "#" || url !== "#") return;
          e.preventDefault();
          const msg =
            l.getAttribute("data-guard-msg") ||
            getMsg("grammar_check_unavailable");
          scheduleError(msg, "click");
          l.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false },
    );
  } catch (_) {}
})();
