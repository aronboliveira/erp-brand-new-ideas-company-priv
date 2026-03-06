/**
 * @fileoverview TypeScript version of public/assets/js/routes/joinUs/change.js
 * @generated from original JavaScript - manual review recommended
 * @module change
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const lang = (
    document.documentElement.getAttribute("lang") ?? "en"
  ).toLowerCase();
  const dict =
    (window.translations &&
      (window.translations[lang] || window.translations[lang.split("-")[0]])) ||
    window.translations?.en ||
    {};
  const tr = k => dict[k] || k;

  const showToastOrAlert = msg => {
    try {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
      const hasBootstrapToast = !!(window.bootstrap && window.bootstrap.Toast);
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
      if (hasBootstrapToast) {
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.style.position = "fixed";
          container.style.top = "1rem";
          container.style.right = "1rem";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        const toastEl = document.createElement("div");
        toastEl.className = "toast align-items-center text-bg-danger border-0";
        toastEl.setAttribute("role", "alert");
        toastEl.setAttribute("aria-live", "assertive");
        toastEl.setAttribute("aria-atomic", "true");
        toastEl.innerHTML = `
                    <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>`;
        container.appendChild(toastEl);
        const toast = new window.bootstrap.Toast(toastEl, { delay: 3500 });
        toast.show();
        setTimeout((): void => { toastEl.remove(); }, 4000);
      } else {
        alert(msg);
      }
    } catch {
      alert(msg);
    }
  };

  const ensure = selector => {
    const el = document.querySelector(selector);
    if (!el) throw new Error(`${tr("element_unavailable")} (${selector})`);
    return el;
  };

  const init = (): void => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!window.jQuery) throw new Error(tr("jquery_unavailable"));
    const $ = window.jQuery;
    const $radios = $("input[name='client_check']");
    const $existWrap = $(".exist_client");
    const $newWrap = $(".new_client");
    const $name = $("#client_name");
    const $email = $("#client_email");
    const $password = $("#client_password");

    const safeToggle = mode => {
      const exist = mode === "exist";
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if ($existWrap.length) $existWrap.toggleClass("d-none", !exist);
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if ($newWrap.length) $newWrap.toggleClass("d-none", exist);
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if ($name.length)
        exist
          ? $name.removeAttr("required")
          : $name.attr("required", "required");
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if ($email.length)
        exist
          ? $email.removeAttr("required")
          : $email.attr("required", "required");
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if ($password.length)
        exist
          ? $password.removeAttr("required")
          : $password.attr("required", "required");
    };

    const current = ($radios.filter(":checked").val() ?? "new").toLowerCase();
    safeToggle(current);

    $(document).on("click", "input[name='client_check']", function (): void {
      try {
        const mode = String($(this).val() ?? "new").toLowerCase();
        safeToggle(mode);
      } catch (e) {
        showToastOrAlert(e.message || tr("request_failed"));
      }
    });
  };

  const start = (): void => {
    try {
      ensure("body");
      init();
    } catch (e) {
      showToastOrAlert(e.message || tr("init_failed"));
    }
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", start, { once: true });
  } else {
    start();
  }
})();

export {};
