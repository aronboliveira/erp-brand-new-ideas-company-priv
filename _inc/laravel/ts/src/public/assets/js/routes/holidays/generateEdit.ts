/**
 * @fileoverview TypeScript version of public/assets/js/routes/holidays/generateEdit.js
 * @generated from original JavaScript - manual review recommended
 * @module generateEdit
 */

/* global bootstrap */
((): void => {
  try {
    const a = document.getElementById("holiday-gen-ai");
    if (!a) return;
    if (a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");
    const toast = (msg: string): void=> {
      const text =
        msg ?? "AI generation route is unavailable. Please contact technical support or your domain administrator.";
      const hasBootstrap = !!(
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap
      );
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      if (hasBootstrap) {
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = text;
        t.appendChild(b);
        container.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(text);
      }
    };
    a.addEventListener("click", (e: Event) => {
      try {
        const href = (a.getAttribute("href") ?? "#").trim();
        const url = (a.getAttribute("data-url") ?? "#").trim();
        if (url === "#" || href === "#") {
          e.preventDefault();
          toast(a.getAttribute("data-guard-msg") ?? "");
          a.setAttribute("data-failed-route", "true");
        }
      } catch {}
    });
  } catch {}
})();

export {};
