(() => {
  try {
    const formEl = document.getElementById("invoice-template-settings-form");
    if (!formEl) {
      return;
    }
    if (formEl.getAttribute("data-listener-active") === "true") {
      return;
    }
    formEl.setAttribute("data-listener-active", "true");
    formEl.addEventListener("submit", e => {
      try {
        const actionUrl = formEl.getAttribute("action") ?? "#";
        const dataUrl = formEl.getAttribute("data-url") ?? actionUrl ?? "#";
        if (dataUrl !== "#" && actionUrl !== "#") {
          return;
        }
        e.preventDefault();
        const guardMsg =
          formEl.getAttribute("data-guard-msg") ??
          "Invoice template settings route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
        );
        let toastContainer = document.getElementById("toast-container");
        if (!toastContainer) {
          toastContainer = document.createElement("div");
          toast          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
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
        formEl.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (err) {}
})();
