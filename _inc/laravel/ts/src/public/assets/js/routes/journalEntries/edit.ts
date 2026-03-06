/**
 * @fileoverview TypeScript version of public/assets/js/routes/journalEntries/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  const toast = m => {
    try {
      const hasBs =
        !!document.querySelector('link[href*="bootstrap"]') &&
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
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
        b.textContent = m;
        t.appendChild(b);
        c.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(m);
      }
    } catch {
      alert(m);
    }
  };

  const fm = document.getElementById("journalEntry-edit-form");
  if (!fm) return;

  fm.addEventListener("submit", e => {
    const url = (
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      fm.getAttribute("data-url") ??
      fm.getAttribute("action") ?? "#"
    ).trim();
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    if (!url || url === "#") {
      e.preventDefault();
      toast(fm.getAttribute("data-guard-msg") ?? "Route unavailable.");
    }
  });

  const cancelBtn = document.querySelector('.modal-footer [value="Cancel"]');
  if (cancelBtn) {
    cancelBtn.addEventListener("click", e => {
      const url = (cancelBtn.getAttribute("data-index-url") ?? "#").trim();
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!url || url === "#") {
        e.preventDefault();
        toast(cancelBtn.getAttribute("data-guard-msg") ?? "Route unavailable.");
      }
    });
  }
})();

export {};
