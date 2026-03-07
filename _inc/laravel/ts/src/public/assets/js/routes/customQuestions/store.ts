/**
 * @fileoverview TypeScript version of public/assets/js/routes/customQuestions/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

/* global bootstrap */
((): void => {
  const form = document.getElementById("custom-question-store-form");
  if (!form || form.getAttribute("data-listener-active") === "true") return;
  form.setAttribute("data-listener-active", "true");

  form.addEventListener("submit", event => {
    try {
      const url = form.getAttribute("data-url");
      if (url && url !== "#") return;
      event.preventDefault();

      const msg = form.getAttribute("data-guard-msg") ?? "# ERROR";
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
      form.setAttribute("data-failed-route", "true");
    } catch (e) {}
  });
})();

export {};
