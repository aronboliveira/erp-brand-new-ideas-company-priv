/**
 * @fileoverview TypeScript version of public/assets/js/routes/customers/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */

((): void => {
  document.querySelectorAll('[id^="customer-edit-btn-"]').forEach(btn => {
    if (!btn || btn.getAttribute("data-listener-active") === "true") return;
    btn.setAttribute("data-listener-active", "true");
    btn.addEventListener("click", (e: Event) => {
      try {
        const url = btn.getAttribute("data-url") ?? "#";
        if (url !== "#") return;
        e.preventDefault();
        const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR";
        const bs =
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        if (bs) {
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
      } catch (__err) {
    console.error(`[edit] Error:`, __err);
  }
    });
  });
})();

export {};
