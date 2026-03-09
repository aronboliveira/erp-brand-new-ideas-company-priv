(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const anchors = Array.from(
      document.querySelectorAll(
        "a.ai-btn[data-ajax-popup-over][data-url][data-guard-msg]",
      ),
    );
    if (!anchors.length) return;
    anchors.forEach(a => {
      if (a.getAttribute("data-click-guarded") === "true") return;
      a.setAttribute("data-click-guarded", "true");
      a.addEventListener("click", e => {
        try {
          const dataUrl = (a.getAttribute("data-url") ?? "#").trim();
          if (dataUrl && dataUrl !== "#") return;
          e.preventDefault();
          const msg =
            a.getAttribute("data-guard-msg") ||
            getMsg("ai_generate_unavailable");
          scheduleError(msg, "click");
          a.setAttribute("data-failed-route", "true");
        } catch {}
      });
    });
  } catch {}
})();
