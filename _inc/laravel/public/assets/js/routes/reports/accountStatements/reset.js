(() => {
  try {
    const btn = document.getElementById("reset-account-statement");
    if (!btn) {
      return;
    }
    if (btn.getAttribute("data-listener-active") === "true") {
      return;
    }
    btn.setAttribute("data-listener-active", "true");
    btn.addEventListener("click", e => {
      try {
        const href = btn.getAttribute("href") ?? "#";
        const url = btn.getAttribute("data-url") ?? href ?? "#";
        if (url !== "#" && href !== "#") {
          return;
        }
        e.preventDefault();
        const msg =
          btn.getAttribute("data-guard-msg") ??
          "Reset account statement route is unavailable. Please contact technical support or your domain administrator.";
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
        btn.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (err) {}
})();
