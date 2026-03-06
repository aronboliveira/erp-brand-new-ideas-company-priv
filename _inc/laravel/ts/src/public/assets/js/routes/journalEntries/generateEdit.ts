/**
 * @fileoverview TypeScript version of public/assets/js/routes/journalEntries/generateEdit.js
 * @generated from original JavaScript - manual review recommended
 * @module generateEdit
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

  const selector = '[data-ajax-popup-over="true"][data-url]';
  const bind = a => {
    if (a.getAttribute("data-gen-guarded") === "true") return;
    a.setAttribute("data-gen-guarded", "true");
    a.addEventListener("click", e => {
      const url = (a.getAttribute("data-url") ?? "#").trim();
      if (!url || url === "#") {
        e.preventDefault();
        toast(
          a.getAttribute("data-guard-msg") ?? "AI generation route unavailable."
        );
      }
    });
  };

  document.querySelectorAll(selector).forEach(bind);
  new MutationObserver((): void => {
    document.querySelectorAll(selector).forEach(bind);
  }).observe(document.body, { childList: true, subtree: true });
})();

export {};
