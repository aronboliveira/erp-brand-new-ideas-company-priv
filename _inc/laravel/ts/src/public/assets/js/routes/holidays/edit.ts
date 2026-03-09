/**
 * @fileoverview TypeScript version of public/assets/js/routes/holidays/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */

((): void => {
  try {
    const fm = document.getElementById("holiday-edit-form");
    if (!fm) return;
    if (fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");
    const toast = (msg: string): void => {
      const text =
          msg ??
          "Update route is unavailable. Please contact technical support or your domain administrator.",
        hasBootstrap = !!(
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
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          t.setAttribute(k, v);
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
    if (!fm.getAttribute("data-listener-bound-submit")) {
      fm.setAttribute("data-listener-bound-submit", "1");
      fm.addEventListener("submit", (e: Event) => {
        try {
          const action = (fm.getAttribute("action") ?? "#").trim(),
            url = (fm.getAttribute("data-url") ?? "#").trim();
          if (url === "#" || action === "#") {
            e.preventDefault();
            toast(fm.getAttribute("data-guard-msg") ?? "");
            fm.setAttribute("data-failed-route", "true");
          }
        } catch (__err) {
          console.error(`[edit] Error:`, __err);
        }
      });
    }
  } catch (__err) {
    console.error(`[edit] Error:`, __err);
  }
})();

export {};
