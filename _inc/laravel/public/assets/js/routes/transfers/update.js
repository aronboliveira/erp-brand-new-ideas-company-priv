(() => {
  try {
    const f = document.getElementById("edit_transfer");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");
    const resolved = f.getAttribute("data-resolved-action") || "#";
    if (
      f.hasAttribute("action") &&
      f.getAttribute("action") === "#" &&
      resolved !== "#"
    ) {
      f.setAttribute("action", resolved);
    }
    f.addEventListener("submit", e => {
      try {
        const action = f.getAttribute("action") || "#";
        if (action !== "#") return;
        e.preventDefault();
        const msgAttr = f.getAttribute("data-guard-msg") || "";
        const msg =
          msgAttr && msgAttr.trim().length
            ? msgAttr
            : "Update transfer route is unavailable. Please contact technical support or your domain administrator.";
        const bsLink = document.querySelector('link[href*="bootstrap"]');
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          document.body.appendChild(container);
        }
        if (bsLink && typeof window.bootstrap !== "undefined") {
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
        f.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (error) {}
})();
