/**
 * @fileoverview TypeScript version of public/assets/js/routes/otherPayments/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */

/* global bootstrap */
((): void => {
  const form = document.getElementById("otherpayment-update-form");
  if (!form) return;

  const showMsg = (msg: string) => {
    try {
      if (window.bootstrap.Toast) {
        const c =
          document.getElementById("toast-container") ??
          ((): HTMLDivElement => {
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
        (c as HTMLElement).appendChild(el);
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
          "Update route is unavailable. Please contact technical support or your domain administrator.";
        showMsg(msg);
      }
    },
    { passive: false },
  );
})();

export {};
