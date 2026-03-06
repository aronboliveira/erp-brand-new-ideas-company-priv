/**
 * @fileoverview TypeScript version of public/assets/js/routes/generics/dashboard.js
 * @generated from original JavaScript - manual review recommended
 * @module dashboard
 */
/* eslint-disable @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  try {
    const l = document.getElementById("dashboard-breadcrumb-link");
    if (!l) return;
    if (l.getAttribute("data-listener-active") === "true") return;
    l.setAttribute("data-listener-active", "true");
    l.addEventListener(
      "click",
      function (e) {
        try {
          const href = l.getAttribute("href") ?? "#";
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          const url = l.getAttribute("data-url") ?? "#";
          if (href !== "#" || url !== "#") return;
          e.preventDefault();
          const msg =
            l.getAttribute("data-guard-msg") ?? "Dashboard route is unavailable. Please contact technical support or your domain administrator.";
          const linkEl = document.querySelector('link[href*="bootstrap"]');
          const hasBs =
            // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain, @typescript-eslint/strict-boolean-expressions
            linkEl !== null && window.bootstrap && window.bootstrap.Toast;
          let container = document.getElementById("toast-container");
          if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className =
              "toast-container position-fixed top-0 end-0 p-3";
            container.style.zIndex = "1080";
            container.className = "position-fixed top-0 end-0 p-3";
            document.body.appendChild(container);
          }
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          if (hasBs) {
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
            const inst = window.bootstrap.Toast.getOrCreateInstance(t);
            t.addEventListener("hidden.bs.toast", function (): void {
              try {
                t.remove();
              } catch (_) {}
            });
            inst.show();
          } else {
            alert(msg);
          }
          l.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false }
    );
  } catch (_) {}
})();

export {};
