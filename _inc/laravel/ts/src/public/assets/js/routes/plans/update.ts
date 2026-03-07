/**
 * @fileoverview TypeScript version of public/assets/js/routes/plans/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */

/* global bootstrap */
((): void => {
  const form = document.getElementById("plan-update-form");
  if (!form) return;

  const toast = (msg: string) => {
    try {
      if (window.bootstrap.Toast) {
        const c =
          document.getElementById("toast-container") ??
          ((): HTMLDivElement => {
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

  form.addEventListener(
    "submit",
    (e: Event) => {
      const url =
        form.getAttribute("action") ?? form.getAttribute("data-url") ?? "#";
      if (!url || url === "#") {
        e.preventDefault();
        const msg =
          form.getAttribute("data-guard-msg") ??
          "Update Plan route is unavailable. Please contact technical support or your domain administrator.";
        toast(msg);
      }
    },
    { passive: false },
  );
})();

export {};
