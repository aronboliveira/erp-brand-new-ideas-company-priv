/**
 * @fileoverview TypeScript version of public/assets/js/routes/partials/admin/menu/payslip.js (renamed to payslip-alt.ts due to TS casing conflict with paySlip.ts)
 * @generated from original JavaScript — automated migration
 * @module payslip
 */
((): void => {
  try {
    const l = document.getElementById("payslip-link");
    if (!l) {
      return;
    }
    if (l.getAttribute("data-listener-active") === "true") {
      return;
    }
    l.setAttribute("data-listener-active", "true");

    const ensureToastContainer = () => {
      let c = document.getElementById("toast-container");
      if (!c) {
        c = document.createElement("div");
        c.id = "toast-container";
        document.body.appendChild(c);
      }
      return c;
    };

    l.addEventListener("click", e => {
      try {
        const href = (l.getAttribute("href") ?? "#").trim();
        const url = (l.getAttribute("data-url") ?? href ?? "#").trim();
        if (url !== "#" && href !== "#") {
          return;
        }

        e.preventDefault();

        const msg = (
          l.getAttribute("data-guard-msg") ??
          "Payslip route is unavailable. Please contact technical support or your domain administrator."
        ).trim();
        const hasBs = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
        );

        if (hasBs) {
          const c = ensureToastContainer();
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
          bootstrap.Toast.getOrCreateInstance(t).show();
        } else {
          alert(msg);
        }

        l.setAttribute("data-failed-route", "true");
      } catch (_err) {}
    });
  } catch (_err) {}
})();
