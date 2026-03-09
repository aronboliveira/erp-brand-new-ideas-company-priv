(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  try {
    const anchors = document.querySelectorAll("a[data-guard-msg][data-url]");
    anchors.forEach(a => {
      if (a.getAttribute("data-listener-active") === "true") return;
      a.setAttribute("data-listener-active", "true");
      const url = a.getAttribute("data-url") ?? "#";
      if (
        (a.getAttribute("href") === "#" || !a.getAttribute("href")) &&
        url !== "#"
      )
        a.setAttribute("href", url);
      a.addEventListener("click", e => {
        const href = a.getAttribute("href") ?? "#";
        if (href && href !== "#") return;
        e.preventDefault();
        const msg =
          a.getAttribute("data-guard-msg") ||
          getMsg("chatify_card_route_unavailable");
        scheduleError(msg, "click");
        a.setAttribute("data-failed-route", "true");
      });
    });
  } catch {}
})();
