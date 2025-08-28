(() => {
  try {
    const l = document.getElementById("termination-generate-link");
    if (!l || l.getAttribute("data-listener-active") === "true") return;
    l.setAttribute("data-listener-active", "true");
    l.addEventListener("click", e => {
      try {
        const href = l.getAttribute("href") || "#";
        const url = l.getAttribute("data-url") || href || "#";
        if (href !== "#" || url !== "#") return;
        e.preventDefault();
        const msgAttr = l.hasAttribute("data-guard-msg")
          ? l.getAttribute("data-guard-msg")
          : "";
        const msg =
          msgAttr && msgAttr.trim().length > 0
            ? msgAttr
            : "Generate termination route is unavailable. Please contact technical support or your domain administrator.";
        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
        const hasBootstrap = !!(
          bootstrapLink &&
          window.bootstrap &&
          typeof window.bootstrap.Toast !== "undefined"
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
          window.bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }
        l.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (error) {}
})();
