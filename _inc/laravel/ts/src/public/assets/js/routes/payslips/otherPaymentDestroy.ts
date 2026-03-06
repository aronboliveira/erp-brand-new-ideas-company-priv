/**
 * @fileoverview TypeScript version of public/assets/js/routes/payslips/otherPaymentDestroy.js
 * @generated from original JavaScript - manual review recommended
 * @module otherPaymentDestroy
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  try {
    const forms =
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
      Array.from(
        document.querySelectorAll(
          'form[id^="other-payment-delete-form-"][data-url][data-guard-msg]'
        )
      ) ?? [];
    forms.forEach(fm => {
      try {
        if (fm.getAttribute("data-submit-guarded") === "true") {
          return;
        }
        fm.setAttribute("data-submit-guarded", "true");
        fm.addEventListener("submit", e => {
          try {
            const action = (fm.getAttribute("action") ?? "#").trim();
            const url = (fm.getAttribute("data-url") ?? "#").trim();
            if (url !== "#" && action !== "#") {
              return;
            }
            e.preventDefault();
            const msg =
              fm.getAttribute("data-guard-msg") ??
              "Delete other payment route is unavailable. Please contact technical support or your domain administrator.";
            const hasBootstrap = !!(
              document.querySelector('link[href*="bootstrap"]') &&
              // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
              window.bootstrap
            );
            let container = document.getElementById("toast-container");
            if (!container) {
              container = document.createElement("div");
              container.id = "toast-container";
              container.className =
                "toast-container position-fixed top-0 end-0 p-3";
              container.style.zIndex = "1080";
              document.body.appendChild(container);
            }
            if (hasBootstrap) {
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
              bootstrap.Toast.getOrCreateInstance(t).show();
            } else {
              alert(msg);
            }
            fm.setAttribute("data-failed-route", "true");
          } catch (err) {}
        });
      } catch (err) {}
    });

    const links =
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
      Array.from(
        document.querySelectorAll(
          'a[id^="other-payment-delete-link-"][data-url][data-guard-msg]'
        )
      ) ?? [];
    links.forEach(l => {
      try {
        if (l.getAttribute("data-listener-active") === "true") {
          return;
        }
        l.setAttribute("data-listener-active", "true");
        l.addEventListener("click", e => {
          try {
            const url = (l.getAttribute("data-url") ?? "#").trim();
            if (url !== "#") {
              return;
            }
            const msg =
              l.getAttribute("data-guard-msg") ??
              "Delete other payment route is unavailable. Please contact technical support or your domain administrator.";
            const hasBootstrap = !!(
              document.querySelector('link[href*="bootstrap"]') &&
              // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
              window.bootstrap
            );
            let container = document.getElementById("toast-container");
            if (!container) {
              container = document.createElement("div");
              container.id = "toast-container";
              container.className =
                "toast-container position-fixed top-0 end-0 p-3";
              container.style.zIndex = "1080";
              document.body.appendChild(container);
            }
            if (hasBootstrap) {
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
              bootstrap.Toast.getOrCreateInstance(t).show();
            } else {
              alert(msg);
            }
            l.setAttribute("data-failed-route", "true");
          } catch (err) {}
        });
      } catch (err) {}
    });
  } catch (err) {}
})();

export {};
