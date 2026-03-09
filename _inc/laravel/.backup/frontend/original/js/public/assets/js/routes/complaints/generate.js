(() => {
  try {
    const link = document.getElementById("ai-generate-complaint-link");
    if (!link) {
      return;
    }
    if (link.getAttribute("data-listener-active") === "true") {
      return;
    }
    link.setAttribute("data-listener-active", "true");
    link.addEventListener("click", e => {
      try {
        const href = link.getAttribute("href") ?? "#";
        const url = link.getAttribute("data-url") ?? href ?? "#";
        if (url !== "#" && href !== "#") {
          return;
        }
        e.preventDefault();
        const msg =
          link.getAttribute("data-guard-msg") ??
          "Generate AI complaint route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
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
        link.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (err) {}
})();
