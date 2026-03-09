(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  try {
    const host = document.documentElement;
    const flag = "data-balance-sheet-vertical-listener";
    if (host.hasAttribute(flag) && host.getAttribute(flag) === "true") return;
    host.setAttribute(flag, "true");
    document.addEventListener(
      "click",
      function (e) {
        try {
          const a =
            e.target &&
            (e.target.closest
              ? e.target.closest("a.balance-sheet-vertical")
              : null);
          if (!a) return;
          const href = a.getAttribute("href") || "#";
          const url = a.getAttribute("data-url") || href || "#";
          if (href !== "#" || url !== "#") return;
          e.preventDefault();
          const msg =
            a.getAttribute("data-guard-msg") ||
            getMsg("view_balance_sheet_unavailable");
          scheduleError(msg, "click");
          a.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false },
    );
  } catch (_) {}
})();
