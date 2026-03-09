(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  const selector = ".delete-custom-field-link[data-route-guard][data-url]";
  const alias = "data-listening-customfieldsdeleteclick";
  document.querySelectorAll(selector).forEach(el => {
    if (!el.hasAttribute(alias)) {
      el.setAttribute(alias, "true");
      el.addEventListener("click", event => {
        if (el.getAttribute(alias) !== "true") return;
        const url = el.getAttribute("data-url");
        if (url === "#" && el.href === "#") {
          event.preventDefault();
          const msg =
            event.currentTarget.getAttribute("data-guard-msg") ||
            getMsg("delete_custom_field_unavailable");
          scheduleError(msg, "click");
        }
      });
    }
  });
})();
