/**
 * @fileoverview TypeScript version of public/assets/js/routes/customers/transactions/apply.js
 * @generated from original JavaScript - manual review recommended
 * @module apply
 */

((): void => {
  const resetBtn = document.getElementById("transaction-apply-btn");
  if (!resetBtn || resetBtn.getAttribute("data-listener-active") === "true")
    return;
  resetBtn.setAttribute("data-listener-active", "true");
  if (!resetBtn.getAttribute("data-listener-bound-click")) {
    resetBtn.setAttribute("data-listener-bound-click", "1");
    resetBtn.addEventListener("click", (e: Event) => {
      try {
        const url = resetBtn.getAttribute("data-url") ?? "#";
        if (url !== "#") return;
        e.preventDefault();
        const msg = resetBtn.getAttribute("data-guard-msg") ?? "# ERROR",
          bs =
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap;
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
        resetBtn.setAttribute("data-failed-route", "true");
      } catch (__err) {
        console.error(`[apply] Error:`, __err);
      }
    });
  }
})();

export {};
