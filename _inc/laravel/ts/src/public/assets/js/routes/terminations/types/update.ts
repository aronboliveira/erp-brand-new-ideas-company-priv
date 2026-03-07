/**
 * @fileoverview TypeScript version of public/assets/js/routes/terminations/types/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */

/* global bootstrap */
((): void => {
  try {
    const f = document.getElementById("termination-type-update-form");
    if (!f || f.getAttribute("data-listener-active") === "true") {
      return;
    }
    f.setAttribute("data-listener-active", "true");

    f.addEventListener("submit", (e: Event) => {
      try {
        const action = f.getAttribute("action") ?? "#";
        const url = f.getAttribute("data-url") ?? "#";
        if (action !== "#" && url !== "#") {
          return;
        }

        e.preventDefault();

        const msgAttr = f.hasAttribute("data-guard-msg")
          ? f.getAttribute("data-guard-msg")
          : "";
        const msg =
          (msgAttr ?? "").trim().length > 0
            ? msgAttr
            : "Update termination type route is unavailable. Please contact technical support or your domain administrator.";

        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
        const hasBootstrap =
          bootstrapLink && typeof window.bootstrap.Toast !== "undefined";

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

        f.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (error) {}
})();

export {};
