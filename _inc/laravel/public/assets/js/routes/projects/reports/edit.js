(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  const selector = ".edit-project-link";
  const alias = "data-listening-editprojectclick";
  document.querySelectorAll(selector).forEach(el => {
    try {
      if (!el.hasAttribute(alias)) {
        el.setAttribute(alias, "true");
        el.addEventListener("click", event => {
          try {
            const url = el.getAttribute("data-url");
            if (url !== "#" && el.href !== "#") return;
            event.preventDefault();
            const msg =
              el.getAttribute("data-guard-msg") ||
              getMsg("edit_project_unavailable");
            scheduleError(msg, "click");
          } catch {}
        });
      }
    } catch {}
  });
})();
