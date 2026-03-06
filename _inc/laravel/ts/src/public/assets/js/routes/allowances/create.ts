/**
 * @fileoverview TypeScript version of public/assets/js/routes/allowances/create.js
 * @generated from original JavaScript - manual review recommended
 * @module create
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  const form = document.getElementById("allowance-store-form");
  if (!form) return;
  const flagAttr = "data-listener-active";
  if (form.getAttribute(flagAttr) === "true") return;
  form.setAttribute(flagAttr, "true");
  form.addEventListener("submit", event => {
    try {
      const url = form.getAttribute("data-url");
      const action = form.getAttribute("action");
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if ((!action || action === "#") && (!url || url === "#")) {
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
      }
    } catch {}
  });
  const observer = new MutationObserver((): void => {
    if (!document.getElementById("allowance-store-form")) observer.disconnect();
  });
  observer.observe(document.body, { childList: true, subtree: true });
})();

export {};
