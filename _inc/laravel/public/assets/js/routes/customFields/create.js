(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  const link = document.getElementById("create-custom-field-link");
  const alias = "data-listening-createclick";
  if (!link || link.hasAttribute(alias)) return;
  link.addEventListener("click", event => {
    if (link.getAttribute(alias) !== "true") return;
    const url = link.getAttribute("data-url");
    if (url !== "#" || link.href !== "#") return;
    event.preventDefault();
    const msg =
      event.currentTarget.getAttribute("data-guard-msg") ||
      getMsg("create_custom_field_unavailable");
    scheduleError(msg, "click");
  });
  link.setAttribute(alias, "true");
})();
