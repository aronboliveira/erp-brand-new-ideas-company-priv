(() => {
  try {
    const form = document.getElementById("edit_resignation");
    if (!form || form.getAttribute("data-listener-active") === "true") {
      return;
    }
    form.setAttribute("data-listener-active", "true");
    form.addEventListener("submit", e => {
      try {
        const action = form.getAttribute("action") || "#";
        const url = form.getAttribute("data-action-url") || action || "#";
        if (action !== "#" && url !== "#") {
          return;
        }
        e.preventDefault();
        const msg =
          form.getAttribute("data-form-guard-msg") ||
          "Update resignation route is unavailable. Please contact technical support or your domain administrator.";
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          document.body.appendChild(container);
        }
        const bootstrapLink =
          document.querySelector('link[href*="bootstrap"]') ||
          document.querySelector('link[href*="bootstrap.min"]');
        const hasBootstrap =
          bootstrapLink !== null &&
          typeof window !== "undefined" &&
          typeof window.bootstrap !== "undefined";
        if (hasBootstrap) {
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
          window.bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }
        form.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (error) {}
})();
