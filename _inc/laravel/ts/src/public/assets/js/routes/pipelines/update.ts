/**
 * @fileoverview TypeScript version of public/assets/js/routes/pipelines/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  const form = document.getElementById("pipeline-update-form");
  if (!form) return;

  const showToast = msg => {
    try {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
      if (window.bootstrap.Toast) {
        const container =
          document.getElementById("toast-container") ??
          ((): void => {
            const c = document.createElement("div");
            c.id = "toast-container";
            document.body.appendChild(c);
            return c;
          })();
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = msg;
        t.appendChild(body);
        container.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t).show();
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
          form.getAttribute("data-guard-msg") ?? "Update Pipeline route is unavailable. Please contact technical support or your domain administrator.";
        showToast(msg);
      }
    },
    { passive: false }
  );
})();

export {};
