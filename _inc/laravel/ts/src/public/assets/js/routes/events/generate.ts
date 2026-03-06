/**
 * @fileoverview TypeScript version of public/assets/js/routes/events/generate.js
 * @generated from original JavaScript - manual review recommended
 * @module generate
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  const toast = m => {
    try {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
      if (window.bootstrap && window.bootstrap.Toast) {
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          c.className = "position-fixed bottom-0 end-0 p-3";
          document.body.appendChild(c);
        }
        const t = document.createElement("div");
        t.className = "toast align-items-center text-bg-danger border-0";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        t.innerHTML =
          '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
        t.querySelector(".toast-body").textContent = m;
        c.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t, { delay: 4000 }).show();
        return;
      }
    } catch (_) {}
    alert(m);
  };
  const ai = document.getElementById("event-generate-ai-link");
  if (ai?.getAttribute("data-listener-active") !== "true") {
    ai.setAttribute("data-listener-active", "true");
    ai.addEventListener(
      "click",
      e => {
        const u = ai.getAttribute("data-url");
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if (!u || u === "#") {
          e.preventDefault();
          toast(ai.getAttribute("data-guard-msg") ?? "#");
        }
      },
      { passive: false }
    );
  }
})();

export {};
