(() => {
  try {
    const link = document.getElementById("ai-generate-company-policy-link");
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
          "Generate AI company policy route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
        );
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
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
