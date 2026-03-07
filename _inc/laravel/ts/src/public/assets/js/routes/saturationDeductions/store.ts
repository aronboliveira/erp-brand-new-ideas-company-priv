/**
 * @fileoverview TypeScript version of public/assets/js/routes/saturationDeductions/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

/* global bootstrap */
((): void => {
  try {
    const f = document.getElementById("store_saturation_deduction_form");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    f.addEventListener("submit", (e: Event) => {
      try {
        const action = f.getAttribute("action") ?? "#";
        const url = f.getAttribute("data-action-url") ?? "#";

        if (url !== "#") return;

        e.preventDefault();

        const msgAttr = "data-form-guard-msg";
        const msg = f.hasAttribute(msgAttr)
          ? f.getAttribute(msgAttr) ?? "Store saturation deduction route is unavailable. Please contact technical support or your domain administrator."
          : "Store saturation deduction route is unavailable. Please contact technical support or your domain administrator.";

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
          typeof window !== "undefined" &&
          typeof window.bootstrap !== "undefined" &&
          !!bootstrapLink;

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
