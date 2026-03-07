/**
 * @fileoverview TypeScript version of public/assets/js/routes/plans/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

/* global bootstrap */
(function (): void {
  const mark = "data-listener-active";

  function toast(message: string): void{
    const text = message ?? "Requested route is unavailable.";
    const hasBs = !!(
      document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
      window.bootstrap
    );
    if (!hasBs) {
      alert(text);
      return;
    }
    let box = document.getElementById("toast-container");
    if (!box) {
      box = document.createElement("div");
      box.id = "toast-container";
      document.body.appendChild(box);
    }
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
  }

  function guardLink(a: Element | null): void{
    if (!a || a.getAttribute(mark) === "true") return;
    a.setAttribute(mark, "true");
    a.addEventListener("click", function (e: Event) {
      const href = (a.getAttribute("href") ?? "#").trim();
      const url = ((a.getAttribute("data-url") || href) ?? "#").trim();
      if (url !== "#" && href !== "#") return;
      e.preventDefault();
      toast(a.getAttribute("data-guard-msg") ?? "");
    });
  }

  function init(): void{
    document
      .querySelectorAll("a[data-guard-msg], a[data-url]")
      .forEach(guardLink);
    try {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (
        el: HTMLElement,
      ) {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch (_) {}
      });
    } catch (_) {}
  }

  document.addEventListener("DOMContentLoaded", function (): void {
    init();
    const mo = new MutationObserver(init);
    mo.observe(document.body, { childList: true, subtree: true });
  });
})();

export {};
