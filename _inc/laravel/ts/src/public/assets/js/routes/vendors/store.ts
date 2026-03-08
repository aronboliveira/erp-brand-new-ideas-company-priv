/**
 * @fileoverview TypeScript version of public/assets/js/routes/vendors/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

((): void => {
  try {
    const f = document.getElementById("vendor-store-form");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") ?? "#";
    const current = f.getAttribute("action");
    if ((current === "#" || !current) && resolved !== "#")
      f.setAttribute("action", resolved);

    f.addEventListener("submit", (e: Event) => {
      const action = f.getAttribute("action") ?? "#";
      if (action && action !== "#") return;
      e.preventDefault();

      const msg =
        f.getAttribute("data-guard-msg") ?? "Store vendor route is unavailable. Please contact technical support or your domain administrator.";
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        document.body.appendChild(container);
      }
      const hasBS =
        document.querySelector('link[href*="bootstrap"]') &&
        window.bootstrap.Toast;
      if (hasBS) {
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
        try {
          window.bootstrap.Toast.getOrCreateInstance(toast).show();
        } catch {
          alert(msg);
        }
      } else {
        alert(msg);
      }
      f.setAttribute("data-failed-route", "true");
    });
  } catch (__err) {
    console.error(`[store] Error:`, __err);
  }
})();

export {};
