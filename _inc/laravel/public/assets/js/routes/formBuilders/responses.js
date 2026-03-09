(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  try {
    const bindGuard = a => {
      if (!a || a.getAttribute("data-listener-active") === "true") return;
      a.setAttribute("data-listener-active", "true");
      a.addEventListener("click", e => {
        try {
          const href = (a.getAttribute("href") ?? "#").trim();
          const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg =
            a.getAttribute("data-guard-msg") ||
            getMsg("form_response_route_unavailable");
          scheduleError(msg, "click");
          a.setAttribute("data-failed-route", "true");
        } catch (err) {}
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
        } catch (e) {}
      });
    } catch (err) {}
  } catch (err) {}
})();
