/**
 * @fileoverview TypeScript version of public/assets/js/routes/customers/invoice.js
 * @generated from original JavaScript - manual review recommended
 * @module invoice
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  document.querySelectorAll('[id^="invoice-show-btn-"]').forEach(btn => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!btn || btn.getAttribute("data-listener-active") === "true") return;
    btn.setAttribute("data-listener-active", "true");
    btn.addEventListener("click", e => {
      try {
        const url = (btn.getAttribute("data-url") ?? "#").trim();
        if (url !== "#") return;
        e.preventDefault();
        const msg = btn.getAttribute("data-guard-msg") ?? "#";
        const hasBs = !!(
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
        );
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        if (hasBs) {
          const t = document.createElement("div");
          t.className = "toast";
          t.setAttribute("role", "alert");
          t.setAttribute("aria-live", "assertive");
          t.setAttribute("aria-atomic", "true");
          const b = document.createElement("div");
          b.className = "toast-body";
          b.textContent = msg;
          t.appendChild(b);
          c.appendChild(t);
          bootstrap.Toast.getOrCreateInstance(t).show();
        } else {
          alert(msg);
        }
        btn.setAttribute("data-failed-route", "true");
      } catch {}
    });
  });
})();

export {};
