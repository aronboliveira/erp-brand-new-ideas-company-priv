/**
 * @fileoverview TypeScript version of public/assets/js/routes/formBuilders/fields.js
 * @generated from original JavaScript - manual review recommended
 * @module fields
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  try {
    const toast = msg => {
      const text =
        msg ?? "Requested route is unavailable. Please contact technical support or your domain administrator.";
      const hasBootstrap = !!(
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
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

    const bindGuard = el => {
      if (!el || el.getAttribute("data-listener-active") === "true") return;
      el.setAttribute("data-listener-active", "true");
      el.addEventListener("click", e => {
        try {
          const href = (el.getAttribute("href") ?? "#").trim();
          const url = (el.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          toast(el.getAttribute("data-guard-msg") ?? "");
          el.setAttribute("data-failed-route", "true");
        } catch (_) {}
      });
    };

    document
      .querySelectorAll("a[data-guard-msg], a[data-url]")
      .forEach(bindGuard);

    try {
      const els = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
      );
      els.forEach((el: Element): void => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch (_) {}
      });
    } catch (_) {}
  } catch (_) {}
})();

export {};
