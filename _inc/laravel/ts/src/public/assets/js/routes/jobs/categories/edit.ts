/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/categories/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */

/* global bootstrap */
((): void => {
  try {
    const fm = document.getElementById("jobCategory-edit-form");
    if (!fm) return;
    if (fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");
    fm.addEventListener("submit", (e: Event) => {
      try {
        const url = (
          fm.getAttribute("data-url") ??
          fm.getAttribute("action") ?? "#"
        ).trim();
        if (!url || url === "#") {
          e.preventDefault();
          const msg = fm.getAttribute("data-guard-msg") ?? "Route unavailable.";
          const hasBs =
            !!document.querySelector('link[href*="bootstrap"]') &&
            !!window.bootstrap.Toast;
          if (hasBs) {
            let c = document.getElementById("toast-container");
            if (!c) {
              c = document.createElement("div");
              c.id = "toast-container";
              document.body.appendChild(c);
            }
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
            window.bootstrap.Toast.getOrCreateInstance(t).show();
          } else {
            alert(msg);
          }
        }
      } catch {}
    });
  } catch {}
})();

export {};
