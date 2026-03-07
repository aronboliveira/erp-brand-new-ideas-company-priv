/**
 * @fileoverview TypeScript version of public/assets/js/routes/deals/createList.js
 * @generated from original JavaScript - manual review recommended
 * @module createList
 */

/* global bootstrap */
((): void => {
  const btn = document.getElementById("deal-create-btn");
  if (!btn || btn.getAttribute("data-listener-active") === "true") return;
  btn.setAttribute("data-listener-active", "true");
  btn.addEventListener("click", (e: Event) => {
    try {
      const url = btn.getAttribute("data-url") ?? "#";
      if (url !== "#") return;
      e.preventDefault();
      const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR";
      const bs =
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      if (bs) {
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
    } catch {}
  });
})();

export {};
