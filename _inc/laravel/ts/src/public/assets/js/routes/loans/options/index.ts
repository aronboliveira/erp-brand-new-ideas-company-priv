/**
 * @fileoverview TypeScript version of public/assets/js/routes/loans/options/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  const DEFAULT_MSG =
    "Requested route is unavailable. Please contact technical support or your domain administrator.";

  const toast = message => {
    const text = message || DEFAULT_MSG;
    const hasBs =
      !!document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      !!window.bootstrap;
    let box = document.getElementById("toast-container");
    if (!box) {
      box = document.createElement("div");
      box.id = "toast-container";
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

  const bindLinkGuard = el => {
    if (!el || el.getAttribute("data-listener-active") === "true") return;
    el.setAttribute("data-listener-active", "true");
    el.addEventListener("click", e => {
      try {
        const href = (el.getAttribute("href") ?? "#").trim();
        const url = (el.getAttribute("data-url") ?? href ?? "#").trim();
        if (url !== "#" && href !== "#") return;
        e.preventDefault();
        toast(el.getAttribute("data-guard-msg") || DEFAULT_MSG);
        el.setAttribute("data-failed-route", "true");
      } catch (_) {}
    });
  };

  const bindFormGuard = fm => {
    if (!fm || fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");
    fm.addEventListener("submit", e => {
      try {
        const action = (fm.getAttribute("action") ?? "#").trim();
        const url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
        if (url !== "#" && action !== "#") return;
        e.preventDefault();
        toast(fm.getAttribute("data-guard-msg") || DEFAULT_MSG);
        fm.setAttribute("data-failed-route", "true");
      } catch (_) {}
    });
  };

  document
    .querySelectorAll("a[data-guard-msg], a[data-url]")
    .forEach(bindLinkGuard);
  document
    .querySelectorAll("form[data-guard-msg], form[data-url]")
    .forEach(bindFormGuard);

  try {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el: Element): void => {
      try {
        bootstrap.Tooltip.getOrCreateInstance(el);
      } catch (_) {}
    });
  } catch (_) {}
})();

export {};
