/**
 * @fileoverview TypeScript version of public/assets/js/routes/travels/generate.js
 * @generated from original JavaScript - manual review recommended
 * @module generate
 */

/* global bootstrap */
(function (): void {
  try {
    const a = document.getElementById("travel-generate-link");
    if (!a) return;
    if (a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");

    const url = a.getAttribute("data-url") ?? "#";
    if (
      (a.getAttribute("href") === "#" || !a.getAttribute("href")) &&
      url !== "#"
    )
      a.setAttribute("href", url);

    a.addEventListener("click", function (e: Event) {
      try {
        const href = a.getAttribute("href") ?? "#";
        if (href !== "#") return;
        e.preventDefault();
        const msg =
          a.getAttribute("data-guard-msg") ?? "Generate content route is unavailable. Please contact technical support or your domain administrator.";
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        const bs =
          typeof window.bootstrap !== "undefined" ? window.bootstrap : null;
        if (bs?.Toast) {
          const t = document.createElement("div");
          t.className = "toast";
          t.setAttribute("role", "alert");
          t.setAttribute("aria-live", "assertive");
          t.setAttribute("aria-atomic", "true");
          const b = document.createElement("div");
          b.className = "toast-body";
          b.textContent = msg;
          t.appendChild(b);
          container.appendChild(t);
          bs.Toast.getOrCreateInstance(t).show();
        } else {
          alert(msg);
        }
        a.setAttribute("data-failed-route", "true");
      } catch {}
    });
  } catch {}
})();

export {};
