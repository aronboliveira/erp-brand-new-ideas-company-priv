/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/stages/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */
/* eslint-disable @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  try {
    const f = document.getElementById("update-project-stage-form");
    if (!f) return;
    const flag = "data-submit-listener";
    if (f.hasAttribute(flag) && f.getAttribute(flag) === "true") return;
    f.setAttribute(flag, "true");
    f.addEventListener(
      "submit",
      function (e) {
        try {
          const action = f.getAttribute("action") ?? "#";
          if (action !== "#") return;
          e.preventDefault();
          const msg =
            f.getAttribute("data-guard-msg") ?? "Update project stage route is unavailable. Please contact technical support or your domain administrator.";
          const hasBootstrap =
            document.querySelector('link[href*="bootstrap"]') &&
            // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain, @typescript-eslint/strict-boolean-expressions
            window.bootstrap &&
            window.bootstrap.Toast;
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
          if (hasBootstrap) {
            const toast = document.createElement("div");
            toast.className = "toast";
            toast.setAttribute("role", "alert");
            toast.setAttribute("aria-live", "assertive");
            toast.setAttribute("aria-atomic", "true");
            const body = document.createElement("div");
            body.className = "toast-body";
            body.textContent = msg;
            toast.appendChild(body);
            container.appendChild(toast);
            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
            toast.addEventListener("hidden.bs.toast", function (): void {
              try {
                toast.remove();
              } catch (_) {}
            });
            inst.show();
          } else {
            alert(msg);
          }
          f.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false }
    );
  } catch (_) {}
})();

export {};
