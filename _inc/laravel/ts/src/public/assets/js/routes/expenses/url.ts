/**
 * @fileoverview TypeScript version of public/assets/js/routes/expenses/url.js
 * @generated from original JavaScript - manual review recommended
 * @module url
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-misused-promises, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const SUCCESS_KEY = "url_copy_success";
  const ERROR_KEY = "url_copy_failed";
  const ATTR_ACTIVE = "data-listener-active";
  const SELECTOR = ".copy_link";

  const showError = msg => {
    const hasBs = window.bootstrap.Toast;
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
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

  const showSuccess = msg => { show_toastr("success", msg, "success"); };

  const getMsg = key => {
    let lang = (
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      sessionStorage.getItem("erp-np-lang") ??
      document.documentElement.lang ?? "en"
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

  els.forEach((el: Element): void => {
    if (el.getAttribute(ATTR_ACTIVE) === "true") return;
    el.setAttribute(ATTR_ACTIVE, "true");

    el.addEventListener("click", async e => {
      e.preventDefault();
      try {
        const href = el.getAttribute("href");
        if (href == null || href === "") throw new Error();
        await navigator.clipboard.writeText(href);
        showSuccess(getMsg(SUCCESS_KEY));
      } catch {
        showError(getMsg(ERROR_KEY));
      }
    });
  });

  const mo = new MutationObserver((_, obs) => {
    if (![...els].some(el => document.body.contains(el))) {
      els.forEach((el: Element): void => { el.removeEventListener("click"); });
      obs.disconnect();
    }
  });
  mo.observe(document.body, { childList: true, subtree: true });
})();

export {};
