(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  try {
    const bindGuard = el => {
      if (!el || el.getAttribute("data-listener-active") === "true") return;
      el.setAttribute("data-listener-active", "true");
      el.addEventListener("click", e => {
        try {
          const href = (el.getAttribute("href") ?? "#").trim();
          const url = (el.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg =
            el.getAttribute("data-guard-msg") ||
            getMsg("form_field_route_unavailable");
          scheduleError(msg, "click");
          el.setAttribute("data-failed-route", "true");
        } catch (_) {}
      });
    };

    document
      .querySelectorAll("a[data-guard-msg], a[data-url]")
      .forEach(bindGuard);

    try {
      const els = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]'),
      );
      els.forEach(el => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch (_) {}
      });
    } catch (_) {}
  } catch (_) {}
})();
