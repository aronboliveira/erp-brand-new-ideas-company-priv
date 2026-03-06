/**
 * @fileoverview TypeScript version of public/assets/js/routes/leaves/changeAction.js
 * @generated from original JavaScript - manual review recommended
 * @module changeAction
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
// assets/js/routes/leaves/changeAction.js
((): void => {
  try {
    const f = document.getElementById("leave-changeaction-form");
    if (!f) return;
    if (
      f.hasAttribute("data-submit-listener") &&
      f.getAttribute("data-submit-listener") === "true"
    )
      return;
    f.setAttribute("data-submit-listener", "true");

    const showNotice = msg => {
      try {
        const linkEl = document.querySelector('link[href*="bootstrap"]');
        const hasBootstrap =
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
        if (hasBootstrap) {
          const toast = document.createElement("div");
          toast.className = "toast";
          toast.setAttribute("role", "alert");
          toast.setAttribute("aria-live", "assertive");
          toast.setAttribute("aria-atomic", "true");
          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent =
            msg ?? "Requested route is unavailable. Please contact technical support or your domain administrator.";
          toast.appendChild(body);
          container.appendChild(toast);
          const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
          toast.addEventListener("hidden.bs.toast", function (): void {
            try {
              toast.remove();
            } catch (e) {}
          });
          inst.show();
        } else {
          alert(
            msg ?? "Requested route is unavailable. Please contact technical support or your domain administrator."
          );
        }
      } catch (_) {}
    };

    f.addEventListener(
      "submit",
      e => {
        try {
          const action = f.getAttribute("action") ?? "#";
          if (action !== "#") return;
          e.preventDefault();
          const msg =
            f.getAttribute("data-guard-msg") ?? "Change leave action route is unavailable. Please contact technical support or your domain administrator.";
          showNotice(msg);
          f.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false }
    );
  } catch (_) {}
})();
