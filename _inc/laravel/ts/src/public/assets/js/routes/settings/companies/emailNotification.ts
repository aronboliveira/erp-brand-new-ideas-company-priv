/**
 * @fileoverview TypeScript version of public/assets/js/routes/settings/companies/emailNotification.js
 * @generated from original JavaScript - manual review recommended
 * @module emailNotification
 */

/* global bootstrap */
((): void => {
  const form = document.getElementById("email-status-language-form");
  if (!form || form.getAttribute("data-listener-active") === "true") return;
  form.setAttribute("data-listener-active", "true");
  form.addEventListener("submit", (e: Event) => {
    try {
      const url = form.getAttribute("data-url") ?? "#";
      if (url !== "#") return;
      e.preventDefault();
      const msg = form.getAttribute("data-guard-msg") ?? "# ERROR";
      const bs =
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      if (bs) {
        const toast = document.createElement("div");
        toast.className = "toast";
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", "assertive");
        toast.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = msg;
        toast.appendChild(body);
        container.appendChild(toast);
        bootstrap.Toast.getOrCreateInstance(toast).show();
      } else {
        alert(msg);
      }
      form.setAttribute("data-failed-route", "true");
    } catch (err) {}
  });
})();

export {};
