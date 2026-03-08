/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/tasks/comments/delete.js
 * @generated from original JavaScript - manual review recommended
 * @module delete
 */

((): void => {
  try {
    const host = document.documentElement;
    const flag = "data-delete-comment-listener";
    if (host.hasAttribute(flag) && host.getAttribute(flag) === "true") return;
    host.setAttribute(flag, "true");
    document.addEventListener(
      "click",
      function (e: Event) {
        try {
          const a =
            e.target &&
            ((e.target as Element).closest
              ? (e.target as Element).closest("a.delete-comment")
              : null);
          if (!a) return;
          const href = a.getAttribute("href") ?? "#";
          const url = (a.getAttribute("data-url") || href) ?? "#";
          if (href !== "#" || url !== "#") return;
          e.preventDefault();
          const msg =
            a.getAttribute("data-guard-msg") ??
            "Destroy project task comment route is unavailable. Please contact technical support or your domain administrator.";
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
    console.error(`[delete] Error:`, _);
  }
            });
            inst.show();
          } else {
            alert(msg);
          }
          a.setAttribute("data-failed-route", "true");
        } catch (_) {
    console.error(`[delete] Error:`, _);
  }
      },
      { passive: false },
    );
  } catch (_) {
    console.error(`[delete] Error:`, _);
  }
})();

export {};
