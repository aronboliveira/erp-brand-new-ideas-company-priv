/**
 * @fileoverview TypeScript version of public/assets/js/routes/companyPolicies/attachment.js
 * @generated from original JavaScript - manual review recommended
 * @module attachment
 */
/* eslint-disable @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";

  const el = document.getElementById("attachment");
  if (!el || el.getAttribute("data-listener-active") === "true") return;
  el.setAttribute("data-listener-active", "true");

  el.addEventListener("change", function (e) {
    try {
      const file = e.target.files?.[0];
      if (!file) return;
      const img = document.getElementById("image");
      if (!img) throw new Error("noIMG");
      img.src = URL.createObjectURL(file);
    } catch {
      let msg = errFb;
      if (
        el.getAttribute("data-sv-localized") === "true" ||
        el.getAttribute(dataClientLocalized) === "true"
      ) {
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        msg = el.getAttribute(dataGuardMsg) ?? errFb;
      } else {
        let lang = (
          // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
          window.sessionStorage.getItem("erp-np-lang") ??
          document.documentElement.lang ?? "en"
        )
          .toLowerCase()
          .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        const key = "preview_failed";
        msg =
          window.translations?.[lang]?.[key] ||
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          el.getAttribute(dataGuardMsg) ||
          window.translations?.en?.[key] ||
          errFb;
        if (msg !== errFb) {
          el.setAttribute(dataGuardMsg, msg);
          el.setAttribute(dataClientLocalized, "true");
        }
      }
      const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
      if (bootstrapLink && window.bootstrap) {
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        const toastEl = document.createElement("div");
        toastEl.className = "toast";
        toastEl.setAttribute("role", "alert");
        toastEl.setAttribute("aria-live", "assertive");
        toastEl.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = msg;
        toastEl.appendChild(body);
        container.appendChild(toastEl);
        bootstrap.Toast.getOrCreateInstance(toastEl).show();
      } else {
        alert(msg);
      }
    }
  });

  const observer = new MutationObserver((): void => {
    if (!document.getElementById("attachment")) observer.disconnect();
  });
  observer.observe(document.body, { childList: true, subtree: true });
})();

export {};
