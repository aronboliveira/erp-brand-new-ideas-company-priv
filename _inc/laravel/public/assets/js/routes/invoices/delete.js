(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const selector = ".delete-invoice-link";
  const alias = "data-listening-deleteinvoiceclick";
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
          const msg =
            el.getAttribute("data-guard-msg") ||
            getMsg("delete_invoice_unavailable");
          scheduleError(msg, "click");
        }
      });
    }
  });
})();
