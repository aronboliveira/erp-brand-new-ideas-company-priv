/**
 * @fileoverview TypeScript version of public/assets/js/routes/resignations/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  try {
    const form = document.getElementById("store_resignation");
    if (!form || form.getAttribute("data-listener-active") === "true") {
      return;
    }
    form.setAttribute("data-listener-active", "true");
    form.addEventListener("submit", e => {
      try {
        const action = form.getAttribute("action") ?? "#";
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        const url = form.getAttribute("data-action-url") ?? "#";
        if (action !== "#" && url !== "#") {
          return;
        }
        e.preventDefault();
        const msg =
          form.getAttribute("data-form-guard-msg") ?? "Store resignation route is unavailable. Please contact technical support or your domain administrator.";
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        const bootstrapLink =
          document.querySelector('link[href*="bootstrap"]') ??
          document.querySelector('link[href*="bootstrap.min"]');
        const hasBootstrap =
          bootstrapLink !== null &&
          typeof window !== "undefined" &&
          typeof window.bootstrap !== "undefined";
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
          window.bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }
        form.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (error) {}
})();

export {};
