(function () {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  function guardClick(a) {
    if (!a || a.getAttribute("data-guard-bound") === "1") return;
    a.setAttribute("data-guard-bound", "1");
    a.addEventListener("click", function (e) {
      const href = (a.getAttribute("href") || "#").trim();
      const url = (a.getAttribute("data-url") || href || "#").trim();
      if (url !== "#" && href !== "#") return;
      e.preventDefault();
      const msg =
        a.getAttribute("data-guard-msg") || getMsg("route_unavailable");
      scheduleError(msg, "click");
    });
  }

  function init() {
    const ids = ["#project-report-index-link"];

    ids.forEach(function (sel) {
      const el = document.querySelector(sel);
      if (el) guardClick(el);
    });

    document
      .querySelectorAll('a[id^="project-report-export-link-"]')
      .forEach(guardClick);
    document
      .querySelectorAll('a[id^="project-task-show-link-"]')
      .forEach(guardClick);
  }

  document.addEventListener("DOMContentLoaded", init);
})();
