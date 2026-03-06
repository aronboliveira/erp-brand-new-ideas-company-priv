/**
 * @fileoverview TypeScript version of public/assets/js/routes/awards/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  const form = document.getElementById("award-update-form");
  if (form?.getAttribute("data-listener-active") !== "true") {
    form.setAttribute("data-listener-active", "true");
    form.addEventListener("submit", event => {
      try {
        const action = form.getAttribute("action");
        const url = form.getAttribute("data-url");
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if ((action && action !== "#") ?? (url && url !== "#")) return;
        event.preventDefault();
        const msg = form.getAttribute("data-guard-msg") ?? "# ERROR";
        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
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
        form.setAttribute("data-failed-route", "true");
      } catch (e) {}
    });
  }

  const link = document.getElementById("award-generate-link");
  if (link?.getAttribute("data-listener-active") !== "true") {
    link.setAttribute("data-listener-active", "true");
    link.addEventListener("click", event => {
      try {
        const href = link.getAttribute("href");
        const url = link.getAttribute("data-url");
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if ((href && href !== "#") ?? (url && url !== "#")) return;
        event.preventDefault();
        const msg = link.getAttribute("data-guard-msg") ?? "# ERROR";
        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
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
        link.setAttribute("data-failed-route", "true");
      } catch (e) {}
    });
  }
})();

export {};
