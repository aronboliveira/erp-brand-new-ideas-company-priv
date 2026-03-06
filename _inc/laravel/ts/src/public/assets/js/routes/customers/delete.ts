/**
 * @fileoverview TypeScript version of public/assets/js/routes/customers/delete.js
 * @generated from original JavaScript - manual review recommended
 * @module delete
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  document.querySelectorAll('[id^="customer-delete-btn-"]').forEach(btn => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!btn || btn.getAttribute("data-listener-active") === "true") return;
    btn.setAttribute("data-listener-active", "true");
    btn.addEventListener("click", e => {
      try {
        const url = btn.getAttribute("data-url") ?? "#";
        if (url !== "#") return;
        e.preventDefault();
        const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR";
        const bs =
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
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
        btn.setAttribute("data-failed-route", "true");
      } catch {}
    });
  });
})();

export {};
