/**
 * @fileoverview TypeScript version of public/assets/js/routes/interviewSchedules/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  try {
    const fm = document.getElementById("interviewSchedule-edit-form");
    if (!fm) return;
    if (fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");
    fm.addEventListener("submit", e => {
      try {
        const url = (
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          fm.getAttribute("data-url") ??
          fm.getAttribute("action") ?? "#"
        ).trim();
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if (!url || url === "#") {
          e.preventDefault();
          const msg = fm.getAttribute("data-guard-msg") ?? "Route unavailable.";
          if (
            // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
            window.bootstrap.Toast &&
            document.querySelector('link[href*="bootstrap"]')
          ) {
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
