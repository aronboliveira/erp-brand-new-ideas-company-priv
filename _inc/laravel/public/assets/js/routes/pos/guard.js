/** @requires ERPGuard */
(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  if (typeof scheduleError !== "function") return;

  const bindGuard = el => {
    if (!el || el.getAttribute("data-listener-active") === "true") return;
    el.setAttribute("data-listener-active", "true");
    el.addEventListener("click", e => {
      const url = el.getAttribute("href") || el.getAttribute("data-url") || "#";
      if (!url || url === "#") {
        e.preventDefault();
        const msg = el.getAttribute("data-guard-msg") || "Action unavailable.";
        scheduleError(msg, "click");
      }
    });
  };

  bindGuard(document.getElementById("pos-print"));
  bindGuard(document.getElementById("pos-setting"));

  try {
    const links = document.querySelectorAll(
      'a[data-guard-msg]:not([data-listener-active="true"])',
    );
    links.forEach(function (a) {
      a.setAttribute("data-listener-active", "true");
      a.addEventListener("click", function (e) {
        const url = a.getAttribute("href") || a.getAttribute("data-url") || "#";
        if (!url || url === "#") {
          e.preventDefault();
          const msg = a.getAttribute("data-guard-msg") || "Action unavailable.";
          scheduleError(msg, "click");
        }
      });
    });
  } catch (_) {}
})();
