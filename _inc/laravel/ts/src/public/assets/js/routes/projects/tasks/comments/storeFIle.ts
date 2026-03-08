/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/tasks/comments/storeFIle.js
 * @generated from original JavaScript - manual review recommended
 * @module storeFIle
 */

((): void => {
  try {
    const b = document.getElementById("file_attachment_submit");
    if (!b) return;
    const flag = "data-click-listener";
    if (b.hasAttribute(flag) && b.getAttribute(flag) === "true") return;
    b.setAttribute(flag, "true");
    b.addEventListener(
      "click",
      function (e: Event) {
        try {
          const url =
            b.getAttribute("data-url") ?? b.getAttribute("data-action") ?? "#";
          if (url !== "#") return;
          e.preventDefault();
          const msg =
            b.getAttribute("data-guard-msg") ??
            "Store file for task comment route is unavailable. Please contact technical support or your domain administrator.";
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
    console.error(`[storeFIle] Error:`, _);
  }
            });
            inst.show();
          } else {
            alert(msg);
          }
          b.setAttribute("data-failed-route", "true");
        } catch (_) {
    console.error(`[storeFIle] Error:`, _);
  }
      },
      { passive: false }
    );
  } catch (_) {
    console.error(`[storeFIle] Error:`, _);
  }
})();

export {};
