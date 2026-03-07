/**
 * @fileoverview TypeScript version of public/assets/js/routes/expenses/url.js
 * @generated from original JavaScript - manual review recommended
 * @module url
 */

/* global bootstrap, $, jQuery */
declare const show_toastr: (type: string, msg: string, status: string) => void;
((): void => {
  const SUCCESS_KEY = "url_copy_success";
  const ERROR_KEY = "url_copy_failed";
  const ATTR_ACTIVE = "data-listener-active";
  const SELECTOR = ".copy_link";

  const showError = (msg: string) => {
    const hasBs = window.bootstrap.Toast;
    if (hasBs) {
      const toast = document.createElement("div");
      toast.className =
        "toast align-items-center text-white bg-danger border-0";
      toast.setAttribute("role", "alert");
      toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
                </div>`;
      document.body.append(toast);
      new bootstrap.Toast(toast).show();
    } else {
      alert(msg);
    }
  };

  const showSuccess = (msg: string) => {
    show_toastr("success", msg, "success");
  };

  const getMsg = (key: string) => {
    let lang = (
      sessionStorage.getItem("erp-np-lang") ??
      document.documentElement.lang ??
      "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");
    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
    return (
      window.translations?.[lang]?.[key] ||
      window.translations?.en?.[key] ||
      "# ERROR"
    );
  };

  const els = document.querySelectorAll(SELECTOR);
  if (els.length === 0) return;

  const handlers = new WeakMap<Element, (e: Event) => Promise<void>>();

  els.forEach((el): void => {
    if (el.getAttribute(ATTR_ACTIVE) === "true") return;
    el.setAttribute(ATTR_ACTIVE, "true");

    const handler = async (e: Event): Promise<void> => {
      e.preventDefault();
      try {
        const href = el.getAttribute("href");
        if (href == null || href === "") throw new Error();
        await navigator.clipboard.writeText(href);
        showSuccess(getMsg(SUCCESS_KEY));
      } catch {
        showError(getMsg(ERROR_KEY));
      }
    };
    handlers.set(el, handler);
    el.addEventListener("click", handler);
  });

  const mo = new MutationObserver((_, obs) => {
    if (![...els].some(el => document.body.contains(el))) {
      els.forEach((el): void => {
        const h = handlers.get(el);
        if (h) el.removeEventListener("click", h);
      });
      obs.disconnect();
    }
  });
  mo.observe(document.body, { childList: true, subtree: true });
})();

export {};
