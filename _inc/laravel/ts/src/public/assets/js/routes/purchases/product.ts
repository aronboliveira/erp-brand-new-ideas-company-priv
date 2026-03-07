/**
 * @fileoverview TypeScript version of public/assets/js/routes/purchases/product.js
 * @generated from original JavaScript - manual review recommended
 * @module product
 */


((): void => {
  try {
    const host = document.documentElement;
    const flag = "data-purchase-product-listener";
    if (host.hasAttribute(flag) && host.getAttribute(flag) === "true") return;
    host.setAttribute(flag, "true");
    document.addEventListener(
      "change",
      function (e: Event) {
        try {
          const sel =
            e.target &&
            ((e.target as Element).closest
              ? (e.target as Element).closest("select.item")
              : null);
          if (!sel) return;
          const url = sel.getAttribute("data-url") ?? "#";
          if (url !== "#") return;
          const msg =
            sel.getAttribute("data-guard-msg") ??
            "Purchase product route is unavailable. Please contact technical support or your domain administrator.";
          const linkEl = document.querySelector('link[href*="bootstrap"]');
          const hasBootstrapToast =
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
            toast.setAttribute("role", "alert");
            toast.setAttribute("aria-live", "assertive");
            toast.setAttribute("aria-atomic", "true");
            const body = document.createElement("div");
            body.className = "toast-body";
            body.textContent = msg;
            toast.appendChild(body);
            container.appendChild(toast);
            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
            toast.addEventListener("hidden.bs.toast", function (): void {
              try {
                toast.remove();
              } catch (_) {}
            });
            inst.show();
          } else {
            alert(msg);
          }
          sel.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: true },
    );
  } catch (_) {}
})();

export {};
