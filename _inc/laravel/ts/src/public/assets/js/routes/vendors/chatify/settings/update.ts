/**
 * @fileoverview TypeScript version of public/assets/js/routes/vendors/chatify/settings/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  try {
    const fm = document.getElementById("update-settings");
    if (!fm) {
      return;
    }
    if (fm.getAttribute("data-listener-active") === "true") {
      return;
    }
    fm.setAttribute("data-listener-active", "true");
    fm.addEventListener("submit", e => {
      try {
        const action = fm.getAttribute("action") ?? "#";
        const url = fm.getAttribute("data-url") ?? "#";
        if (url !== "#" && action !== "#") {
          return;
        }
        e.preventDefault();
        const msg =
          fm.getAttribute("data-guard-msg") ??
          "Update avatar route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
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
