/**
 * @fileoverview TypeScript version of public/assets/js/routes/companyPolicies/preview.js
 * @generated from original JavaScript - manual review recommended
 * @module preview
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const ERR_KEY = "image_preview_failed";
  const ATTACH_SELECTOR = "#attachment";
  const IMAGE_SELECTOR = "#image";
  const attachEl = document.querySelector(ATTACH_SELECTOR);
  const imageEl = document.querySelector(IMAGE_SELECTOR);
  if (!attachEl || !imageEl) return;

  const showError = msg => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    const hasBs = window.bootstrap && typeof bootstrap.Toast === "function";
    if (hasBs) {
      const toastEl = document.createElement("div");
      toastEl.className =
        "toast align-items-center text-white bg-danger border-0";
      toastEl.setAttribute("role", "alert");
      toastEl.innerHTML = `
            <div class="d-flex">
            <div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
            </div>`;
      document.body.append(toastEl);
      new bootstrap.Toast(toastEl).show();
    } else {
      alert(msg);
    }
  };

  const handler = e => {
    try {
      const file = e.target.files?.[0];
      if (!file) return;
      imageEl.src = URL.createObjectURL(file);
    } catch {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msg =
        window.translations?.[lang]?.[ERR_KEY] ||
        window.translations?.en?.[ERR_KEY] ||
        "# ERROR";
      showError(msg);
    }
  };

  attachEl.addEventListener("change", handler, false);

  const mo = new MutationObserver((_, obs) => {
    if (!document.body.contains(attachEl)) {
      attachEl.removeEventListener("change", handler);
      obs.disconnect();
    }
  });
  mo.observe(document.body, { childList: true, subtree: true });
})();

export {};
