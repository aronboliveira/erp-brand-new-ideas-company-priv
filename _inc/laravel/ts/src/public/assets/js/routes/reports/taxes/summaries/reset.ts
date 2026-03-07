/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/taxes/summaries/reset.js
 * @generated from original JavaScript - manual review recommended
 * @module reset
 */

/* global bootstrap */
((): void => {
  try {
    const btn = document.getElementById("reset-tax-summary");
    if (!btn) {
      return;
    }
    if (btn.getAttribute("data-listener-active") === "true") {
      return;
    }
    btn.setAttribute("data-listener-active", "true");
    btn.addEventListener("click", (e: Event) => {
      try {
        const href = btn.getAttribute("href") ?? "#";
        const url = btn.getAttribute("data-url") ?? "#";
        if (url !== "#" && href !== "#") {
          return;
        }
        e.preventDefault();
        const msg =
          btn.getAttribute("data-guard-msg") ??
          "Reset tax summary route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
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
        btn.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (err) {}
})();

export {};
