// assets/js/routes/invoices/edit.js
(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const selector = ".edit-invoice-link";
  const alias = "data-listening-editinvoiceclick";

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
            getMsg("edit_invoice_unavailable");
          scheduleError(msg, "click");
        }
      });
    }
  });
})();
