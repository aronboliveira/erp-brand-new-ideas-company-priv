/**
 * @fileoverview TypeScript version of public/assets/js/routes/settings/companies/noc.js
 * @generated from original JavaScript - manual review recommended
 * @module noc
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  try {
    const links = document.querySelectorAll(".noc-language-link");
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!links || links.length === 0) return;
    links.forEach(l => {
      if (l.getAttribute("data-listener-active") === "true") return;
      l.setAttribute("data-listener-active", "true");
      l.addEventListener("click", e => {
        try {
          const url = l.getAttribute("data-url") ?? "#";
          if (url !== "#") return;
          e.preventDefault();
          const msg = l.getAttribute("data-guard-msg") ?? "# ERROR";
          const hasBootstrap =
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap;
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
          l.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    });
  } catch (err) {}
})();

export {};
