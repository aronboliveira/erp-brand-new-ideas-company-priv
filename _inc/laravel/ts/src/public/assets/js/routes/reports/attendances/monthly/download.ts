/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/attendances/monthly/download.js
 * @generated from original JavaScript - manual review recommended
 * @module download
 */


((): void => {
  try {
    const host = document.documentElement;
    const flag = "data-download-monthly-attendance-listener";
    if (host.hasAttribute(flag) && host.getAttribute(flag) === "true") return;
    host.setAttribute(flag, "true");

    document.addEventListener(
      "click",
      function (e: Event) {
        try {
          const a =
            e.target &&
            ((e.target as Element).closest
              ? (e.target as Element).closest("a.download-monthly-attendance")
              : null);
          if (!a) return;

          const href = a.getAttribute("href") ?? "#";
          if (href !== "#") return;

          e.preventDefault();

          const fnName = a.getAttribute("data-func-name") ?? "";
          const fn =
            typeof window !== "undefined" && fnName
              ? (window as unknown as Record<string, unknown>)[fnName]
              : null;

          if (typeof fn === "function") {
            try {
              (fn as () => void)();
            } catch (_) {}
            return;
          }

          const msg =
            a.getAttribute("data-guard-msg") ??
            "Download function for monthly attendance reports is unavailable. Please contact technical support or your domain administrator.";
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

          a.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false },
    );
  } catch (_) {}
})();

export {};
