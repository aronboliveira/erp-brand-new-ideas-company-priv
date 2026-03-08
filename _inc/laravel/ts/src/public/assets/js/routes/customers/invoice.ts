/**
 * @fileoverview TypeScript version of public/assets/js/routes/customers/invoice.js
 * @generated from original JavaScript - manual review recommended
 * @module invoice
 */

((): void => {
  document.querySelectorAll('[id^="invoice-show-btn-"]').forEach(btn => {
    if (!btn || btn.getAttribute("data-listener-active") === "true") return;
    btn.setAttribute("data-listener-active", "true");
    btn.addEventListener("click", (e: Event) => {
      try {
        const url = (btn.getAttribute("data-url") ?? "#").trim();
        if (url !== "#") return;
        e.preventDefault();
        const msg = btn.getAttribute("data-guard-msg") ?? "#";
        const hasBs = !!(
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
          for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  t.setAttribute(k, v);
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
      } catch (__err) {
    console.error(`[invoice] Error:`, __err);
  }
    });
  });
})();

export {};
