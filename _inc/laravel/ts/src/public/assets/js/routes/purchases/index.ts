/**
 * @fileoverview TypeScript version of public/assets/js/routes/purchases/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

((): void => {
  try {
    const id = "purchase-index-breadcrumb-link";
    const l = document.getElementById(id);
    if (!l) return;
    const flag = "data-click-listener";
    if (l.hasAttribute(flag) && l.getAttribute(flag) === "true") return;
    l.setAttribute(flag, "true");
    l.addEventListener(
      "click",
      function (e: Event) {
        try {
          const href = l.getAttribute("href") ?? "#";
          const url = l.getAttribute("data-url") ?? "#";
          if (href !== "#" || url !== "#") return;
          e.preventDefault();
          const msg =
            l.getAttribute("data-guard-msg") ?? "Purchase index route is unavailable. Please contact technical support or your domain administrator.";
          const linkEl = document.querySelector('link[href*="bootstrap"]');
          const hasBootstrapToast =
            window.bootstrap &&
            typeof window.bootstrap.Toast === "function";
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
            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
            toast.addEventListener("hidden.bs.toast", function (): void {
              try {
                toast.remove();
              } catch (_) {
    console.error(`[index] Error:`, _);
  }
            });
            inst.show();
          } else {
            alert(msg);
          }
          l.setAttribute("data-failed-route", "true");
        } catch (_) {
    console.error(`[index] Error:`, _);
  }
      },
      { passive: false }
    );
  } catch (_) {
    console.error(`[index] Error:`, _);
  }
})();

export {};
