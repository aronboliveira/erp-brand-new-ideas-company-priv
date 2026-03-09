/**
 * @fileoverview TypeScript version of public/assets/js/routes/products/services/generateEdit.js
 * @generated from original JavaScript - manual review recommended
 * @module generateEdit
 */

((): void => {
  const btn = document.querySelector(
    'a[data-url][data-guard-msg][data-sv-localized="true"].btn-icon',
  );
  if (!btn) return;
  const toast = (msg: string): void => {
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
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          el.setAttribute(k, v);
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
    (e: Event) => {
      const url = btn.getAttribute("data-url") ?? "#";
      if (!url || url === "#") {
        e.preventDefault();
        const msg =
          btn.getAttribute("data-guard-msg") ??
          "Generate content route for Product/Service is unavailable. Please contact technical support or your domain administrator.";
        toast(msg);
      }
    },
    { passive: false },
  );
})();

export {};
