(() => {
  try {
    const el = document.getElementById("resignation-generate-link");
    if (!el || el.getAttribute("data-listener-active") === "true") {
      return;
    }
    el.setAttribute("data-listener-active", "true");
    el.addEventListener("click", e => {
      try {
        const href = el.getAttribute("href") || "#";
        const url = el.getAttribute("data-url") || href || "#";
        if (href !== "#" || url !== "#") {
          return;
        }
        e.preventDefault();
        const msg =
          el.getAttribute("data-guard-msg") ||
          "Generate resignation route is unavailable. Please contact technical support or your domain administrator.";
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
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
        el.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (error) {}
})();
