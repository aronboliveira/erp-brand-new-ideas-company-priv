/**
 * @fileoverview TypeScript version of public/assets/js/routes/settings/bills/settings.js
 * @generated from original JavaScript - manual review recommended
 * @module settings
 */

((): void => {
  try {
    const billTemplateSettingsForm = document.getElementById(
      "bill-template-settings-form",
    );
    if (!billTemplateSettingsForm) return;
    if (
      billTemplateSettingsForm.getAttribute("data-listener-active") === "true"
    )
      return;
    billTemplateSettingsForm.setAttribute("data-listener-active", "true");

    if (!billTemplateSettingsForm.getAttribute("data-listener-bound-submit")) {
      billTemplateSettingsForm.setAttribute("data-listener-bound-submit", "1");
      billTemplateSettingsForm.addEventListener("submit", (e: Event) => {
        try {
          const actionUrl =
              billTemplateSettingsForm.getAttribute("action") ?? "#",
            dataUrl = billTemplateSettingsForm.getAttribute("data-url") ?? "#";
          if (dataUrl !== "#" && actionUrl !== "#") return;
          e.preventDefault();

          const guardMsg =
              billTemplateSettingsForm.getAttribute("data-guard-msg") ??
              "Bill template settings route is unavailable. Please contact technical support or your domain administrator.",
            hasBootstrap = !!(
              document.querySelector('link[href*="bootstrap"]') &&
              window.bootstrap
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
              role: "alert",
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

          billTemplateSettingsForm.setAttribute("data-failed-route", "true");
        } catch (err) {
          console.error(`[settings] Error:`, err);
        }
      });
    }
  } catch (err) {
    console.error(`[settings] Error:`, err);
  }
})();

export {};
