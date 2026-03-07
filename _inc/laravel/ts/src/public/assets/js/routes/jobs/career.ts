/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/career.js
 * @generated from original JavaScript - manual review recommended
 * @module career
 */

/* global bootstrap */
((): void => {
  const Q = (s: string) => document.querySelector(s);
  const QA = (s: string) => Array.from(document.querySelectorAll(s));
  const DEFAULT_ROUTE_MSG =
    "Requested route is unavailable. Please contact technical support or your domain administrator.";
  const toast = (message: string) => {
    const text = message || DEFAULT_ROUTE_MSG;
    const hasBs = !!(
      document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
      window.bootstrap
    );
    let box = document.getElementById("toast-container");
    if (!box) {
      box = document.createElement("div");
      box.id = "toast-container";
      box.style.position = "fixed";
      box.style.top = "1rem";
      box.style.right = "1rem";
      box.style.zIndex = "1060";
      document.body.appendChild(box);
    }
    if (hasBs) {
      const t = document.createElement("div");
      t.className = "toast";
      t.setAttribute("role", "alert");
      t.setAttribute("aria-live", "assertive");
      t.setAttribute("aria-atomic", "true");
      const b = document.createElement("div");
      b.className = "toast-body";
      b.textContent = text;
      t.appendChild(b);
      box.appendChild(t);
      bootstrap.Toast.getOrCreateInstance(t).show();
    } else {
      alert(text);
    }
  };
  const bindLinkGuard = (a: Element) => {
    if (!a || a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");
    a.addEventListener("click", (e: Event) => {
      const href = (a.getAttribute("href") ?? "#").trim();
      const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
      if (url !== "#" && href !== "#") return;
      e.preventDefault();
      toast(a.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      a.setAttribute("data-failed-route", "true");
    });
  };
  const bindFormGuard = (f: Element) => {
    if (!f || f.getAttribute("data-submit-guarded") === "true") return;
    f.setAttribute("data-submit-guarded", "true");
    f.addEventListener("submit", (e: Event) => {
      const action = (f.getAttribute("action") ?? "#").trim();
      const url = (f.getAttribute("data-url") ?? action ?? "#").trim();
      if (url !== "#" && action !== "#") return;
      e.preventDefault();
      toast(f.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      f.setAttribute("data-failed-route", "true");
    });
  };
  const initTooltips = (): void => {
    try {
      QA('[data-bs-toggle="tooltip"]').forEach((el: Element): void => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch (_) {}
      });
    } catch (_) {}
  };
  document.addEventListener("DOMContentLoaded", (): void => {
    QA("a[data-guard-msg], a[data-url]").forEach(bindLinkGuard);
    QA("form[data-guard-msg], form[data-url]").forEach(bindFormGuard);
    initTooltips();
  });
})();

export {};
