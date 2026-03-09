/** @requires ERPGuard */
(() => {
  try {
    const { scheduleError } = window.ERPGuard ?? {};
    if (typeof scheduleError !== "function") return;

    const host = document.documentElement;
    const flag = "data-open-quarterly-cashflow-listener";
    if (host.hasAttribute(flag) && host.getAttribute(flag) === "true") return;
    host.setAttribute(flag, "true");

    document.addEventListener(
      "click",
      function (e) {
        try {
          const a =
            e.target &&
            (e.target.closest
              ? e.target.closest("a.quarterly-cashflow-link")
              : null);
          if (!a) return;

          const href = a.getAttribute("href") || "#";
          const url = a.getAttribute("data-url") || href || "#";

          if (url !== "#") return;

          e.preventDefault();

          const msg =
            a.getAttribute("data-guard-msg") ||
            "Quarterly cashflow report route is unavailable. Please contact technical support or your domain administrator.";

          scheduleError(msg, "click");
          a.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false },
    );
  } catch (_) {}
})();
