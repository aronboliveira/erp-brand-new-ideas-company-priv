/**
 * @fileoverview TypeScript version of public/assets/js/routes/travels/create.js
 * @generated from original JavaScript - manual review recommended
 * @module create
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  try {
    const a = document.getElementById("travel-create-link");
    if (!a) return;
    if (a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");

    const url = a.getAttribute("data-url") ?? "#";
    const href = a.getAttribute("href") ?? "#";
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    if ((href === "#" || !href) && url && url !== "#") {
      a.setAttribute("href", url);
    }

    a.addEventListener("click", e => {
      try {
        const currentHref = a.getAttribute("href") ?? "#";
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if (currentHref && currentHref !== "#") return;

        e.preventDefault();
        const msg =
          a.getAttribute("data-guard-msg") ?? "Create travel route is unavailable. Please contact technical support or your domain administrator.";

        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }

        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
        if (window.bootstrap && window.bootstrap.Toast) {
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
          window.bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }

        a.setAttribute("data-failed-route", "true");
      } catch {}
    });
  } catch {}
})();

export {};
