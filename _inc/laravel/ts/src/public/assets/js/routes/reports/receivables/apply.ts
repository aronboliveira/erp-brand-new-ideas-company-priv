/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/receivables/apply.js
 * @generated from original JavaScript - manual review recommended
 * @module apply
 */

((): void => {
  try {
    const btn = document.getElementById("apply-receivables-index");
    if (!btn) {
      return;
    }
    if (btn.getAttribute("data-listener-active") === "true") {
      return;
    }
    btn.setAttribute("data-listener-active", "true");
    btn.addEventListener("click", (e: Event) => {
      try {
        const formId = btn.getAttribute("data-form-id") ?? "";
        const form = formId ? document.getElementById(formId) : null;
        if (!form) {
          return;
        }
        const url = form.getAttribute("data-url") ?? "#";
        if (url === "#") {
          e.preventDefault();
          const msg =
            btn.getAttribute("data-guard-msg") ??
            "Apply receivables route is unavailable. Please contact technical support or your domain administrator.";
          const hasBootstrap = !!(
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
            bootstrap.Toast.getOrCreateInstance(toast).show();
          } else {
            alert(msg);
          }
          btn.setAttribute("data-failed-route", "true");
          return;
        }
        e.preventDefault();
        (form as HTMLFormElement).submit();
      } catch (err) {
    console.error(`[apply] Error:`, err);
  }
    });
  } catch (err) {
    console.error(`[apply] Error:`, err);
  }
})();

export {};
