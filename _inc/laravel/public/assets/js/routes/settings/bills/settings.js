(() => {
  try {
    const billTemplateSettingsForm = document.getElementById(
      "bill-template-settings-form",
    );
    if (!billTemplateSettingsForm) {
      return;
    }
    if (
      billTemplateSettingsForm.getAttribute("data-listener-active") === "true"
    ) {
      return;
    }
    billTemplateSettingsForm.setAttribute("data-listener-active", "true");

    billTemplateSettingsForm.addEventListener("submit", e => {
      try {
        const actionUrl =
          billTemplateSettingsForm.getAttribute("action") ?? "#";
        const dataUrl =
          billTemplateSettingsForm.getAttribute("data-url") ?? actionUrl ?? "#";
        if (dataUrl !== "#" && actionUrl !== "#") {
          return;
        }
        e.preventDefault();

        const guardMsg =
          billTemplateSettingsForm.getAttribute("data-guard-msg") ??
          "Bill template settings route is unavailable. Please contact technical support or your domain administrator.";
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
          toast.setAttribute("role", "alert");
          toast.setAttribute("aria-live", "assertive");
          toast.setAttribute("aria-atomic", "true");

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
      } catch (err) {}
    });
  } catch (err) {}
})();
