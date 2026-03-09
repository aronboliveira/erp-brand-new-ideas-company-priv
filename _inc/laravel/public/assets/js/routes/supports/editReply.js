(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  try {
    const links = document.querySelectorAll("a.support-edit");
    if (!links || !links.length) return;
    links.forEach(l => {
      try {
        if (l.getAttribute("data-listener-active") === "true") return;
        l.setAttribute("data-listener-active", "true");
        l.addEventListener("click", e => {
          try {
            const href = l.getAttribute("href") ?? "#";
            const url = l.getAttribute("data-url") ?? href ?? "#";
            if (url !== "#" && href !== "#") return;
            e.preventDefault();
            const msg =
              l.getAttribute("data-guard-msg") ||
              getMsg("edit_support_unavailable");
            scheduleError(msg, "click");
            l.setAttribute("data-failed-route", "true");
          } catch (err) {}
        });
      } catch (err) {}
    });
  } catch (err) {}
})();
