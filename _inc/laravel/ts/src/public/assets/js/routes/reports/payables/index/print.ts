/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/payables/index/print.js
 * @generated from original JavaScript - manual review recommended
 * @module print
 */


((): void => {
  try {
    const f = document.getElementById("payables-print-form");
    if (!f) return;
    const flag = "data-submit-listener";
    if (f.hasAttribute(flag) && f.getAttribute(flag) === "true") return;
    f.setAttribute(flag, "true");
    f.addEventListener(
      "submit",
      function (e: Event) {
        try {
          const action = f.getAttribute("action") ?? "#";
          const url = f.getAttribute("data-url") ?? "#";
          if (action !== "#" && url !== "#") return;
          e.preventDefault();
          const msg =
            f.getAttribute("data-guard-msg") ?? "Payables print route is unavailable. Please contact technical support or your domain administrator.";
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
          f.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false }
    );
  } catch (_) {}
})();

export {};
