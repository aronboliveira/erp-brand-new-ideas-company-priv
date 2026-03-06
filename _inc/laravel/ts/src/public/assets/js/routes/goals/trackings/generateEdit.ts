/**
 * @fileoverview TypeScript version of public/assets/js/routes/goals/trackings/generateEdit.js
 * @generated from original JavaScript - manual review recommended
 * @module generateEdit
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  try {
    const guardToast = msg => {
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
    const aiBtn = document.getElementById("goal-ai-generate-btn");
    if (!aiBtn) return;
    if (aiBtn.getAttribute("data-listener-active") === "true") return;
    aiBtn.setAttribute("data-listener-active", "true");
    aiBtn.addEventListener("click", e => {
      try {
        const href = (aiBtn.getAttribute("href") ?? "#").trim();
        const url = (aiBtn.getAttribute("data-url") ?? "#").trim();
        if (url !== "#" && href !== "#") return;
        e.preventDefault();
        guardToast(aiBtn.getAttribute("data-guard-msg") ?? "");
        aiBtn.setAttribute("data-failed-route", "true");
      } catch {}
    });
  } catch {}
})();

export {};
