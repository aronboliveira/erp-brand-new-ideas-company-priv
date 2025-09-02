(() => {
  try {
    const ifr = document.getElementById("proposal-template-preview-frame");
    if (!ifr) {
      return;
    }
    if (ifr.getAttribute("data-listener-active") === "true") {
      return;
    }
    ifr.setAttribute("data-listener-active", "true");
    const src = ifr.getAttribute("src") ?? "#";
    const url = ifr.getAttribute("data-url") ?? src ?? "#";
    if (url !== "#" && src !== "#") {
      return;
    }
    const msg =
      ifr.getAttribute("data-guard-msg") ??
      "Proposal preview route is unavailable. Please contact technical support or your domain administrator.";
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
    ifr.setAttribute("data-failed-route", "true");
  } catch (err) {}
})();
