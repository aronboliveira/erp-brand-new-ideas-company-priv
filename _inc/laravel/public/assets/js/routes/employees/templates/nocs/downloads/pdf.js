(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const links = Array.from(
      document.querySelectorAll(
        'a[id^="noc-download-pdf-btn-"][data-url][data-guard-msg]',
      ),
    );
    if (!links || !links.length) {
      return;
    }
    links.forEach(l => {
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
            getMsg("download_pdf_unavailable");
          scheduleError(msg, "click");
          l.setAttribute("data-failed-route", "true");
        } catch (_) {}
      });
    });
  } catch (_) {}
})();
