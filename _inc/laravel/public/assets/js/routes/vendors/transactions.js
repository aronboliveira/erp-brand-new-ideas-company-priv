/** @requires ERPGuard */
(() => {
  try {
    const { scheduleError } = window.ERPGuard ?? {};
    if (typeof scheduleError !== "function") return;

    const f = document.getElementById("vendor-transaction-filter-form");
    if (f && f.getAttribute("data-listener-active") !== "true") {
      f.setAttribute("data-listener-active", "true");
      const resolved = f.getAttribute("data-resolved-action") || "#";
      if (
        (f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
        resolved !== "#"
      ) {
        f.setAttribute("action", resolved);
      }
      f.addEventListener("submit", e => {
        const action = f.getAttribute("action") || "#";
        if (action && action !== "#") return;
        e.preventDefault();
        const msg =
          f.getAttribute("data-guard-msg") ||
          "Vendor transaction route is unavailable. Please contact technical support or your domain administrator.";
        scheduleError(msg, "submit");
        f.setAttribute("data-failed-route", "true");
      });
    }

    const reset = document.getElementById("vendor-transaction-reset-link");
    if (reset && reset.getAttribute("data-listener-active") !== "true") {
      reset.setAttribute("data-listener-active", "true");
      const url = reset.getAttribute("data-url") || "#";
      if (
        (reset.getAttribute("href") === "#" || !reset.getAttribute("href")) &&
        url !== "#"
      ) {
        reset.setAttribute("href", url);
      }
      reset.addEventListener("click", e => {
        const href = reset.getAttribute("href") || "#";
        if (href && href !== "#") return;
        e.preventDefault();
        const msg =
          reset.getAttribute("data-guard-msg") ||
          "Vendor transaction route is unavailable. Please contact technical support or your domain administrator.";
        scheduleError(msg, "click");
        reset.setAttribute("data-failed-route", "true");
      });
    }
  } catch {}
})();
