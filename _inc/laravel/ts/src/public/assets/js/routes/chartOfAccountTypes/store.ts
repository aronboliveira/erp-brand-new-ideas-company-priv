/**
 * @fileoverview TypeScript version of public/assets/js/routes/chartOfAccountTypes/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unused-vars, no-inner-declarations */

/* global bootstrap */
(function (): void {
  try {
    const f = document.getElementById("store_chart_of_account_type");
    if (!f) return;
    const guardMsg = f.getAttribute("data-guard-msg") ?? "Route unavailable";
    const actionHref = f.getAttribute("data-action-href") ?? "";
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    if (!f.getAttribute("action") && actionHref && actionHref !== "#")
      f.setAttribute("action", actionHref);

    function toastOrAlert(msg) {
      try {
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
        const hasBootstrap = !!(window.bootstrap && window.bootstrap.Toast);
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        if (!hasBootstrap) {
          alert(msg);
          return;
        }
        let t = document.getElementById("route-guard-toast");
        if (!t) {
          t = document.createElement("div");
          t.id = "route-guard-toast";
          t.className =
            "toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
          t.setAttribute("role", "alert");
          t.setAttribute("aria-live", "assertive");
          t.setAttribute("aria-atomic", "true");
          t.innerHTML =
            '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
          document.body.appendChild(t);
        }
        const body = t.querySelector(".toast-body");
        if (body) body.textContent = msg;
        new window.bootstrap.Toast(t, { delay: 4000 }).show();
      } catch (e) {
        alert(msg);
      }
    }

    f.addEventListener("submit", function (e) {
      try {
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        const a = f.getAttribute("action") ?? "";
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if (!a || a === "#") {
          e.preventDefault();
          toastOrAlert(guardMsg);
        }
      } catch (_) {
        e.preventDefault();
        alert(guardMsg);
      }
    });
  } catch (_) {}
})();

export {};
