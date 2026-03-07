/**
 * @fileoverview TypeScript version of public/assets/js/routes/customers/transactions/reset.js
 * @generated from original JavaScript - manual review recommended
 * @module reset
 */

/* global bootstrap */
((): void => {
  const resetBtn = document.getElementById("transaction-reset-btn");
  if (!resetBtn || resetBtn.getAttribute("data-listener-active") === "true")
    return;
  resetBtn.setAttribute("data-listener-active", "true");
  resetBtn.addEventListener("click", (e: Event) => {
    try {
      const url = resetBtn.getAttribute("data-url") ?? "#";
      if (url !== "#") return;
      e.preventDefault();
      const msg = resetBtn.getAttribute("data-guard-msg") ?? "# ERROR";
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
      resetBtn.setAttribute("data-failed-route", "true");
    } catch {}
  });
})();

export {};
