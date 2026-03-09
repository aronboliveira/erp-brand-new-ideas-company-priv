/**
 * @fileoverview TypeScript version of public/assets/js/routes/features/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

((): void => {
  try {
    const fm = document.getElementById("feature-store-form");
    if (!fm) return;
    if (fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");

    if (!fm.getAttribute("data-listener-bound-submit")) {
      fm.setAttribute("data-listener-bound-submit", "1");
      fm.addEventListener("submit", (e: Event) => {
        try {
          const action = (fm.getAttribute("action") ?? "#").trim(),
            url = (fm.getAttribute("data-url") ?? "#").trim();
          if (url !== "#" && action !== "#") return;
          e.preventDefault();

          const msg = (
              fm.getAttribute("data-guard-msg") ??
              "Store feature route is unavailable. Please contact technical support or your domain administrator."
            ).trim(),
            hasBootstrap = !!(
              document.querySelector('link[href*="bootstrap"]') &&
              window.bootstrap
            );
          let container = document.getElementById("toast-container");
          if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            document.body.appendChild(container);
          }

          if (hasBootstrap) {
            const toast = document.createElement("div");
            toast.className = "toast";
            for (const [k, v] of Object.entries({
              role: "alert",
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

          fm.setAttribute("data-failed-route", "true");
        } catch (err) {
          console.error(`[store] Error:`, err);
        }
      });
    }
  } catch (err) {
    console.error(`[store] Error:`, err);
  }
})();

export {};
