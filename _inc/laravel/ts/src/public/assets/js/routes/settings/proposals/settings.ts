/**
 * @fileoverview TypeScript version of public/assets/js/routes/settings/proposals/settings.js
 * @generated from original JavaScript - manual review recommended
 * @module settings
 */

((): void => {
  try {
    const formEl = document.getElementById("proposal-template-settings-form");
    if (!formEl) {
      return;
    }
    if (formEl.getAttribute("data-listener-active") === "true") {
      return;
    }
    formEl.setAttribute("data-listener-active", "true");
    formEl.addEventListener("submit", (e: Event) => {
      try {
        const actionUrl = formEl.getAttribute("action") ?? "#";
        const dataUrl = formEl.getAttribute("data-url") ?? "#";
        if (dataUrl !== "#" && actionUrl !== "#") {
          return;
        }
        e.preventDefault();
        const guardMsg =
          formEl.getAttribute("data-guard-msg") ??
          "Proposal template settings route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
        );
        let toastContainer = document.getElementById("toast-container");
        if (!toastContainer) {
          toastContainer = document.createElement("div");
          toastContainer.id = "toast-container";
          toastContainer.className =
            "toast-container position-fixed top-0 end-0 p-3";
          toastContainer.style.zIndex = "1080";
          document.body.appendChild(toastContainer);
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
          const toastBody = document.createElement("div");
          toastBody.className = "toast-body";
          toastBody.textContent = guardMsg;
          toast.appendChild(toastBody);
          toastContainer.appendChild(toast);
          bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(guardMsg);
        }
        formEl.setAttribute("data-failed-route", "true");
      } catch (err) {
    console.error(`[settings] Error:`, err);
  }
    });
  } catch (err) {
    console.error(`[settings] Error:`, err);
  }
})();

export {};
