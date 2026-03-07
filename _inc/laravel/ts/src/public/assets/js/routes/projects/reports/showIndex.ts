/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/reports/showIndex.js
 * @generated from original JavaScript - manual review recommended
 * @module showIndex
 */

/* global bootstrap */
((): void => {
  const link = document.getElementById("project-report-index-link");
  if (!link || link.getAttribute("data-listener-active") === "true") return;
  link.setAttribute("data-listener-active", "true");
  link.addEventListener("click", (e: Event) => {
    try {
      const url = link.getAttribute("data-url") ?? "#";
      if (url !== "#") return;
      e.preventDefault();
      const msg = link.getAttribute("data-guard-msg") ?? "# ERROR";
      const hasBootstrap =
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
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
      link.setAttribute("data-failed-route", "true");
    } catch (err) {}
  });
})();

export {};
