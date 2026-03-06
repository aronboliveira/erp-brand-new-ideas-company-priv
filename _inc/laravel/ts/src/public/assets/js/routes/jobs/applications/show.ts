/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applications/show.js
 * @generated from original JavaScript - manual review recommended
 * @module show
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  const D = m => {
      const t =
          m ?? "Requested route is unavailable. Please contact technical support or your domain administrator.",
        hasBs = !!(
          document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          window.bootstrap
        );
      let box = document.getElementById("toast-container");
      if (!box) {
        box = document.createElement("div");
        box.id = "toast-container";
        document.body.appendChild(box);
      }
      if (hasBs) {
        const el = document.createElement("div");
        el.className = "toast";
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = t;
        el.appendChild(b);
        box.appendChild(el);
        bootstrap.Toast.getOrCreateInstance(el).show();
      } else {
        alert(t);
      }
    },
    bindLink = a => {
      if (!a || a.getAttribute("data-listener-active") === "true") return;
      a.setAttribute("data-listener-active", "true");
      a.addEventListener("click", e => {
        const href = (a.getAttribute("href") ?? "#").trim();
        const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
        if (url !== "#" && href !== "#") return;
        e.preventDefault();
        D(a.getAttribute("data-guard-msg") ?? "");
      });
    },
    bindForm = f => {
      if (!f || f.getAttribute("data-submit-guarded") === "true") return;
      f.setAttribute("data-submit-guarded", "true");
      f.addEventListener("submit", e => {
        const action = (f.getAttribute("action") ?? "#").trim();
        const url = (f.getAttribute("data-url") ?? action ?? "#").trim();
        if (url !== "#" && action !== "#") return;
        e.preventDefault();
        D(f.getAttribute("data-guard-msg") ?? "");
      });
    },
    tips = (): void => {
      try {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el: Element): void => {
          try {
            bootstrap.Tooltip.getOrCreateInstance(el);
          } catch (_) {}
        });
      } catch (_) {}
    };
  document.addEventListener("DOMContentLoaded", (): void => {
    document
      .querySelectorAll("a[data-guard-msg],a[data-url]")
      .forEach(bindLink);
    document
      .querySelectorAll("form[data-guard-msg],form[data-url]")
      .forEach(bindForm);
    tips();
  });
})();

export {};
