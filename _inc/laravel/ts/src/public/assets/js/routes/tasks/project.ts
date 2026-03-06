/**
 * @fileoverview TypeScript version of public/assets/js/routes/tasks/project.js
 * @generated from original JavaScript - manual review recommended
 * @module project
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  try {
    const el = document.getElementById("project-index-back-btn");
    if (!el) {
      return;
    }
    if (el.getAttribute("data-listener-active") === "true") {
      return;
    }
    el.setAttribute("data-listener-active", "true");
    el.addEventListener("click", e => {
      try {
        const href = el.getAttribute("href") ?? "#";
        const url = el.getAttribute("data-url") ?? "#";
        if (url !== "#" && href !== "#") {
          return;
        }
        e.preventDefault();
        const msg =
          el.getAttribute("data-guard-msg") ??
          "Project index route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
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
        el.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (err) {}
})();

export {};
