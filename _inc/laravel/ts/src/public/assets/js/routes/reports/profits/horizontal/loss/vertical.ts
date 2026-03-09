/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/profits/horizontal/loss/vertical.js
 * @generated from original JavaScript - manual review recommended
 * @module vertical
 */

((): void => {
  try {
    const el = document.getElementById("profit-loss-vertical-view");
    if (!el) return;
    if (el.getAttribute("data-listener-active") === "true") return;
    el.setAttribute("data-listener-active", "true");
    if (!el.getAttribute("data-listener-bound-click")) {
      el.setAttribute("data-listener-bound-click", "1");
      el.addEventListener("click", (e: Event) => {
        try {
          const href = el.getAttribute("href") ?? "#",
            url = el.getAttribute("data-url") ?? "#";
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg =
              el.getAttribute("data-guard-msg") ??
              "Vertical profit & loss view route is unavailable. Please contact technical support or your domain administrator.",
            hasBootstrap = !!(
              document.querySelector('link[href*="bootstrap"]') &&
              window.bootstrap
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
          el.setAttribute("data-failed-route", "true");
        } catch (err) {
          console.error(`[vertical] Error:`, err);
        }
      });
    }
  } catch (err) {
    console.error(`[vertical] Error:`, err);
  }
})();

export {};
