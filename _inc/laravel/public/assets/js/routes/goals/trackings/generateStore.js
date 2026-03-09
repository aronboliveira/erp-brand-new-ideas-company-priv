(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const links = Array.from(
      document.querySelectorAll(
        "a.ai-btn[data-ajax-popup-over][data-url][data-guard-msg]",
      ),
    );
    if (!links.length) return;
    links.forEach(a => {
      if (a.getAttribute("data-click-guarded") === "true") return;
      a.setAttribute("data-click-guarded", "true");
      a.addEventListener("click", e => {
        try {
          const href = (a.getAttribute("href") ?? "#").trim();
          const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg =
            a.getAttribute("data-guard-msg") ||
            getMsg("generate_content_unavailable");
          scheduleError(msg, "click");
          a.setAttribute("data-failed-route", "true");
        } catch (_) {}
      });
    });
  } catch (_) {}
})();
