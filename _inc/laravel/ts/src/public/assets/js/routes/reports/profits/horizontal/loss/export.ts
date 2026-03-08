/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/profits/horizontal/loss/export.js
 * @generated from original JavaScript - manual review recommended
 * @module export
 */

((): void => {
  try {
    const form = document.getElementById("profit-loss-export");
    if (!form) {
      return;
    }
    if (form.getAttribute("data-listener-active") === "true") {
      return;
    }
    form.setAttribute("data-listener-active", "true");
    form.addEventListener("submit", (e: Event) => {
      try {
        const url = form.getAttribute("data-url") ?? "#";
        if (url !== "#") {
          return;
        }
        e.preventDefault();
        const msg =
          form.getAttribute("data-guard-msg") ??
          "Export profit and loss route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
        );
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        if (hasBootstrap) {
          const toast = document.createElement("div");
          toast.className = "toast";
          for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toast.setAttribute(k, v);
          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent = msg;
          toast.appendChild(body);
          container.appendChild(toast);
          bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }
        form.setAttribute("data-failed-route", "true");
      } catch (err) {
    console.error(`[export] Error:`, err);
  }
    });
  } catch (err) {
    console.error(`[export] Error:`, err);
  }
})();

export {};
