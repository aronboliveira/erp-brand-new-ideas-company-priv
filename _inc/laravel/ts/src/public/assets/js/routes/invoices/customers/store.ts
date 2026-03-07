/**
 * @fileoverview TypeScript version of public/assets/js/routes/invoices/customers/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

/* global bootstrap */
((): void => {
  const selectEl = document.getElementById("customer");
  if (!selectEl || selectEl.getAttribute("data-listener-active") === "true")
    return;
  selectEl.setAttribute("data-listener-active", "true");
  selectEl.addEventListener("change", event => {
    try {
      const url = selectEl.getAttribute("data-url");
      if (url && url !== "#") return;
      event.preventDefault();

      const msg = selectEl.getAttribute("data-guard-msg") ?? "# ERROR";
      const bootstrapLink = document.querySelector('link[href*="bootstrap"]');

      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }

      if (bootstrapLink && window.bootstrap) {
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

      selectEl.setAttribute("data-failed-route", "true");
    } catch (e) {}
  });
})();

export {};
