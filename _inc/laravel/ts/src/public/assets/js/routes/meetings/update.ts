/**
 * @fileoverview TypeScript version of public/assets/js/routes/meetings/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  const form = document.getElementById("meeting-update-form");
  if (!form) return;

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
          form.getAttribute("data-guard-msg") ?? "Update route is unavailable. Please contact technical support or your domain administrator.";

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
            window.bootstrap.Toast.getOrCreateInstance(toastEl).show();
          } else {
            alert(msg);
          }
        } catch {
          alert(msg);
        }
      }
    },
    { passive: false }
  );
})();

export {};
