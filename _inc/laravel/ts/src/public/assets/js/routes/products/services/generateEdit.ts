/**
 * @fileoverview TypeScript version of public/assets/js/routes/products/services/generateEdit.js
 * @generated from original JavaScript - manual review recommended
 * @module generateEdit
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  const btn = document.querySelector(
    'a[data-url][data-guard-msg][data-sv-localized="true"].btn-icon'
  );
  if (!btn) return;

  const toast = msg => {
    try {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
      if (window.bootstrap.Toast) {
        const c =
          document.getElementById("toast-container") ??
          ((): void => {
            const t = document.createElement("div");
            t.id = "toast-container";
            document.body.appendChild(t);
            return t;
          })();
        const el = document.createElement("div");
        el.className = "toast";
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = msg;
        el.appendChild(body);
        c.appendChild(el);
        window.bootstrap.Toast.getOrCreateInstance(el).show();
      } else {
        alert(msg);
      }
    } catch {
      alert(msg);
    }
  };

  btn.addEventListener(
    "click",
    e => {
      const url = btn.getAttribute("data-url") ?? "#";
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!url || url === "#") {
        e.preventDefault();
        const msg =
          btn.getAttribute("data-guard-msg") ?? "Generate content route for Product/Service is unavailable. Please contact technical support or your domain administrator.";
        toast(msg);
      }
    },
    { passive: false }
  );
})();

export {};
