/**
 * @fileoverview TypeScript version of public/assets/js/routes/customFields/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

((): void => {
  const form = document.getElementById("custom-field-store-form");
  if (!form || form.getAttribute("data-listener-active") === "true") return;
  form.setAttribute("data-listener-active", "true");
  form.addEventListener("submit", event => {
    try {
      const url = form.getAttribute("data-url") ?? "#";
      if (url !== "#") return;
      event.preventDefault();
      const msg = form.getAttribute("data-guard-msg") ?? "# ERROR";
      const bsLink = document.querySelector('link[href*="bootstrap"]');
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      if (bsLink && window.bootstrap) {
        const toastEl = document.createElement("div");
        toastEl.className = "toast";
        for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toastEl.setAttribute(k, v);
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
    } catch (e) {
    console.error(`[store] Error:`, e);
  }
  });
})();

export {};
