/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/cashflow/quarterly/open.js
 * @generated from original JavaScript - manual review recommended
 * @module open
 */

((): void => {
  try {
    const host = document.documentElement,
      flag = "data-open-quarterly-cashflow-listener";
    if (host.hasAttribute(flag) && host.getAttribute(flag) === "true") return;
    host.setAttribute(flag, "true");

    document.addEventListener(
      "click",
      function (e: Event) {
        try {
          const a =
            e.target &&
            ((e.target as Element).closest
              ? (e.target as Element).closest("a.quarterly-cashflow-link")
              : null);
          if (!a) return;
          const href = a.getAttribute("href") ?? "#",
            url = (a.getAttribute("data-url") || href) ?? "#";
          if (url !== "#") return;

          e.preventDefault();

          const msg =
              a.getAttribute("data-guard-msg") ??
              "Quarterly cashflow report route is unavailable. Please contact technical support or your domain administrator.",
            linkEl = document.querySelector('link[href*="bootstrap"]'),
            hasBootstrapToast =
              window.bootstrap && typeof window.bootstrap.Toast === "function";
          let container = document.getElementById("toast-container");
          if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className =
              "toast-container position-fixed top-0 end-0 p-3";
            container.style.zIndex = "1080";
            container.className = "position-fixed top-0 end-0 p-3";
            document.body.appendChild(container);
          }

          if (linkEl && hasBootstrapToast) {
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

            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
            toast.addEventListener("hidden.bs.toast", function (): void {
              try {
                toast.remove();
              } catch (_) {
                console.error(`[open] Error:`, _);
              }
            });
            inst.show();
          } else {
            alert(msg);
          }

          a.setAttribute("data-failed-route", "true");
        } catch (_) {
          console.error(`[open] Error:`, _);
        }
      },
      { passive: false },
    );
  } catch (_) {
    console.error(`[open] Error:`, _);
  }
})();

export {};
