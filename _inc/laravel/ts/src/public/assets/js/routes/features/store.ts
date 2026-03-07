/**
 * @fileoverview TypeScript version of public/assets/js/routes/features/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

/* global bootstrap */
((): void => {
  try {
    const fm = document.getElementById("feature-store-form");
    if (!fm) {
      return;
    }
    if (fm.getAttribute("data-submit-guarded") === "true") {
      return;
    }
    fm.setAttribute("data-submit-guarded", "true");

    fm.addEventListener("submit", (e: Event) => {
      try {
        const action = (fm.getAttribute("action") ?? "#").trim();
        const url = (fm.getAttribute("data-url") ?? "#").trim();
        if (url !== "#" && action !== "#") {
          return;
        }
        e.preventDefault();

        const msg = (
          fm.getAttribute("data-guard-msg") ??
          "Store feature route is unavailable. Please contact technical support or your domain administrator."
        ).trim();
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
        );

        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
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
          bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }

        fm.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (err) {}
})();

export {};
