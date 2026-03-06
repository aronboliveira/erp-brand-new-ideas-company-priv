/**
 * @fileoverview TypeScript version of public/assets/js/routes/invoices/customers/qrcode.js
 * @generated from original JavaScript - manual review recommended
 * @module qrcode
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  const wrapper = document.querySelector(
    '[data-listener-alias="qrcode-copy-link"]'
  );
  if (!wrapper || wrapper.getAttribute("data-listener-active") === "true")
    return;
  wrapper.setAttribute("data-listener-active", "true");
  wrapper.addEventListener("click", event => {
    try {
      const url = wrapper.getAttribute("data-url") ?? "#";
      if (url !== "#") return;
      event.preventDefault();
      const msg = wrapper.getAttribute("data-guard-msg") ?? "# ERROR";
      const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
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
      wrapper.setAttribute("data-failed-route", "true");
    } catch (e) {}
  });
})();

export {};
