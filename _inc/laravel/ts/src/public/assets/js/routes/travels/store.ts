/**
 * @fileoverview TypeScript version of public/assets/js/routes/travels/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */
/* eslint-disable @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
(function (): void {
  try {
    const form = document.getElementById("create_travel");
    if (!form) return;
    if (form.getAttribute("data-listener-active") === "true") return;
    form.setAttribute("data-listener-active", "true");

    form.addEventListener("submit", function (e) {
      try {
        const action = form.getAttribute("action") ?? "#";
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if (!action || action === "#") {
          e.preventDefault();
          const msg =
            form.getAttribute("data-guard-msg") ?? "Store travel route is unavailable. Please contact technical support or your domain administrator.";
          let container = document.getElementById("toast-container");
          if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className =
              "toast-container position-fixed top-0 end-0 p-3";
            container.style.zIndex = "1080";
            document.body.appendChild(container);
          }
          const bs =
            typeof window.bootstrap !== "undefined" ? window.bootstrap : null;
          if (bs?.Toast) {
            const t = document.createElement("div");
            t.className = "toast";
            t.setAttribute("role", "alert");
            t.setAttribute("aria-live", "assertive");
            t.setAttribute("aria-atomic", "true");
            const b = document.createElement("div");
            b.className = "toast-body";
            b.textContent = msg;
            t.appendChild(b);
            container.appendChild(t);
            bs.Toast.getOrCreateInstance(t).show();
          } else {
            alert(msg);
          }
          return;
        }
        const sd = form.querySelector('input[name="start_date"]');
        const ed = form.querySelector('input[name="end_date"]');
        if (sd && ed && sd.value && ed.value) {
          const s = new Date(sd.value);
          const en = new Date(ed.value);
          if (en < s) {
            e.preventDefault();
            const m = "End Date cannot be earlier than Start Date.";
            let c = document.getElementById("toast-container");
            if (!c) {
              c = document.createElement("div");
              c.id = "toast-container";
              document.body.appendChild(c);
            }
            const bs2 =
              typeof window.bootstrap !== "undefined" ? window.bootstrap : null;
            if (bs2?.Toast) {
              const t2 = document.createElement("div");
              t2.className = "toast";
              t2.setAttribute("role", "alert");
              t2.setAttribute("aria-live", "assertive");
              t2.setAttribute("aria-atomic", "true");
              const b2 = document.createElement("div");
              b2.className = "toast-body";
              b2.textContent = m;
              t2.appendChild(b2);
              c.appendChild(t2);
              bs2.Toast.getOrCreateInstance(t2).show();
            } else {
              alert(m);
            }
            return;
          }
        }
      } catch {}
    });
  } catch {}
})();

export {};
