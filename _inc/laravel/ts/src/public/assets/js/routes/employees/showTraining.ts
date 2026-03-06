/**
 * @fileoverview TypeScript version of public/assets/js/routes/employees/showTraining.js
 * @generated from original JavaScript - manual review recommended
 * @module showTraining
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  try {
    const a = document.getElementById("employee-show-link");
    if (!a) return;
    if (a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");

    const url = a.getAttribute("data-url") ?? "#";
    if (
      a.hasAttribute("href") &&
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      (a.getAttribute("href") === "#" || !a.getAttribute("href")) &&
      url !== "#"
    ) {
      a.setAttribute("href", url);
    }

    a.addEventListener("click", e => {
      try {
        const href = a.getAttribute("href") ?? "#";
        if (href !== "#") return;
        e.preventDefault();
        const msg =
          a.getAttribute("data-guard-msg") ?? "Show employee route is unavailable. Please contact technical support or your domain administrator.";
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        const bs = document.querySelector('link[href*="bootstrap"]');
        if (bs && typeof window.bootstrap !== "undefined") {
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
          window.bootstrap.Toast.getOrCreateInstance(t).show();
        } else {
          alert(msg);
        }
        a.setAttribute("data-failed-route", "true");
      } catch (_) {}
    });
  } catch (_) {}
})();

export {};
