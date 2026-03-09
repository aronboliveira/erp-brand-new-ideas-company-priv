(() => {
  try {
    const f = document.getElementById("form-checklist");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");
    f.addEventListener("submit", e => {
      try {
        const url = f.getAttribute("data-url") || "#";
        const action = f.getAttribute("action") || "#";
        if (url !== "#" || action !== "#") return;
        e.preventDefault();
        const msg =
          f.getAttribute("data-guard-msg") ||
          "Store task checklist route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap =
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
          bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }
        f.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (error) {}
})();
