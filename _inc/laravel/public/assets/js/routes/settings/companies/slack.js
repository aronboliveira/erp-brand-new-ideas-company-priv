(() => {
  const form = document.getElementById("slack-setting");
  if (!form || form.getAttribute("data-listener-active") === "true") return;
  form.setAttribute("data-listener-active", "true");
  form.addEventListener("submit", e => {
    try {
      const url = form.getAttribute("data-url") || "#";
      const action = form.getAttribute("action") || "#";
      if (url !== "#" || action !== "#") return;
      e.preventDefault();
      const msg = form.getAttribute("data-guard-msg") || "# ERROR";
      const hasBootstrap =
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
      form.setAttribute("data-failed-route", "true");
    } catch (err) {}
  });
})();
