/**
 * @fileoverview TypeScript version of public/assets/js/routes/goals/trackings/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */

/* global bootstrap */
((): void => {
  try {
    const toast = (msg: string) => {
      const text =
        msg ??
        "Update route is unavailable. Please contact technical support or your domain administrator.";
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

    const fm = document.getElementById("goal-tracking-edit-form");
    if (fm && fm.getAttribute("data-submit-guarded") !== "true") {
      fm.setAttribute("data-submit-guarded", "true");
      fm.addEventListener("submit", (e: Event) => {
        try {
          const action = (fm.getAttribute("action") ?? "#").trim();
          const url = (fm.getAttribute("data-url") ?? "#").trim();
          if (url === "#" || action === "#") {
            e.preventDefault();
            toast(fm.getAttribute("data-guard-msg") ?? "");
            fm.setAttribute("data-failed-route", "true");
          }
        } catch {}
      });
    }

    const range = document.getElementById(
      "goal-progress-range",
    ) as HTMLInputElement | null;
    const out = document.getElementById("goal-progress-output");
    if (range && out) {
      const sync = (): void => {
        try {
          out.textContent = String(range.value ?? "0");
        } catch {}
      };
      range.addEventListener("input", sync);
      range.addEventListener("change", sync);
      sync();
    }

    try {
      const els = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]'),
      );
      els.forEach((el: Element): void => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch {}
      });
    } catch {}
  } catch {}
})();

export {};
