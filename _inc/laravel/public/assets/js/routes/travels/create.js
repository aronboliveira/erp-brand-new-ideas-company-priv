(() => {
  try {
    const a = document.getElementById("travel-create-link");
    if (!a) return;
    if (a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");

    const url = a.getAttribute("data-url") || "#";
    const href = a.getAttribute("href") || "#";
    if ((href === "#" || !href) && url && url !== "#") {
      a.setAttribute("href", url);
    }

    a.addEventListener("click", e => {
      try {
        const currentHref = a.getAttribute("href") || "#";
        if (currentHref && currentHref !== "#") return;

        e.preventDefault();
        const msg =
          a.getAttribute("data-guard-msg") ||
          "Create travel route is unavailable. Please contact technical support or your domain administrator.";

        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          document.body.appendChild(container);
        }

        if (window.bootstrap && window.bootstrap.Toast) {
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

        a.setAttribute("data-failed-route", "true");
      } catch {}
    });
  } catch {}
})();
