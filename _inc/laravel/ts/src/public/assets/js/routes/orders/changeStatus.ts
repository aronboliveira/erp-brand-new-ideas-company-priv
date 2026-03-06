/**
 * @fileoverview TypeScript version of public/assets/js/routes/orders/changeStatus.js
 * @generated from original JavaScript - manual review recommended
 * @module changeStatus
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  const form = document.getElementById("order-change-status-form");
  if (!form) return;

  const showMsg = msg => {
    try {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
      if (window.bootstrap.Toast) {
        const c =
          document.getElementById("toast-container") ??
          ((): void => {
            const d = document.createElement("div");
            d.id = "toast-container";
            document.body.appendChild(d);
            return d;
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

  form.addEventListener(
    "submit",
    e => {
      const url =
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        form.getAttribute("action") ?? form.getAttribute("data-url") ?? "#";
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!url || url === "#") {
        e.preventDefault();
        const msg =
          form.getAttribute("data-guard-msg") ?? "Change status route is unavailable. Please contact technical support or your domain administrator.";
        showMsg(msg);
      }
    },
    { passive: false }
  );
})();

export {};
