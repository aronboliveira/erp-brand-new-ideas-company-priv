/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applications/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return */

/* global bootstrap */
((): void => {
  const Q = s => document.querySelector(s),
    QA = s => Array.from(document.querySelectorAll(s));
  const T = m => {
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
  };
  const bindLink = a => {
    if (!a || a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");
    a.addEventListener("click", e => {
      const href = (a.getAttribute("href") ?? "#").trim();
      const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
      if (url !== "#" && href !== "#") return;
      e.preventDefault();
      T(a.getAttribute("data-guard-msg") ?? "");
    });
  };
  const bindForm = f => {
    if (!f || f.getAttribute("data-submit-guarded") === "true") return;
    f.setAttribute("data-submit-guarded", "true");
    f.addEventListener("submit", e => {
      const action = (f.getAttribute("action") ?? "#").trim();
      const url = (f.getAttribute("data-url") ?? action ?? "#").trim();
      if (url !== "#" && action !== "#") return;
      e.preventDefault();
      T(f.getAttribute("data-guard-msg") ?? "");
    });
  };
  const tips = (): void => {
    try {
      QA('[data-bs-toggle="tooltip"]').forEach((el: Element): void => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch (_) {}
      });
    } catch (_) {}
  };
  const updateCounts = (): void => {
    QA(".kanban-box").forEach(box => {
      const cnt = box.querySelectorAll("> .card").length;
      const header = box.closest(".card")?.querySelector(".card-header .count");
      if (header) header.textContent = String(cnt);
    });
  };
  const initDragula = (): void => {
    const wrap = Q(".kanban-wrapper");
    if (!wrap || typeof dragula !== "function") return;
    let ids = [];
    try {
      ids = JSON.parse(wrap.getAttribute("data-containers") ?? "[]");
    } catch (_) {
      ids = [];
    }
    const containers = ids
      .map(id => document.getElementById(id))
      .filter(Boolean);
    if (containers.length === 0) return;
    dragula(containers).on("drop", (): void => {
      updateCounts();
    });
  };
  document.addEventListener("DOMContentLoaded", (): void => {
    QA("a[data-guard-msg],a[data-url]").forEach(bindLink);
    QA("form[data-guard-msg],form[data-url]").forEach(bindForm);
    tips();
    updateCounts();
    initDragula();
  });
})();

export {};
