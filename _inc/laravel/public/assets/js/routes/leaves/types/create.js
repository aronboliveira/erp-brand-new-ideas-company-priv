(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const selector = ".create-leavetype-link";
  const alias = "data-listening-createleavetypeclick";

  document.querySelectorAll(selector).forEach(el => {
    if (!el.hasAttribute(alias)) {
      el.setAttribute(alias, "true");
      el.addEventListener("click", event => {
        const url = el.getAttribute("data-url");
        const href = el.href
          .replace(window.location.origin, "")
          .replace(window.location.pathname, "");
        if ((!url || url === "#") && (!href || href === "#")) {
          event.preventDefault();
          const msg = el.getAttribute("data-guard-msg") || getMsg("create_leavetype_unavailable");
          scheduleError(msg, "click");
      });
    }
  });
})();
