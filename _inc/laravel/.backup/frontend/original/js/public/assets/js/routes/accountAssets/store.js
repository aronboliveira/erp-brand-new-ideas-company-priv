(() => {
  try {
    const f = document.getElementById("store-account-asset-form");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");
    const resolved = f.getAttribute("data-resolved-action") || "#";
    const guardMsg =
      f.getAttribute("data-guard-msg") ||
      "Store account asset route is unavailable. Please contact technical support or your domain administrator.";
    if (resolved !== "#") {
      if (f.hasAttribute("action") && f.getAttribute("action") === "#") {
        f.setAttribute("action", resolved);
      }
    }
    f.addEventListener("submit", e => {
      try {
        const action = f.getAttribute("action") || "#";
        if (action !== "#") return;
        e.preventDefault();
        const hasBootstrap =
          document.querySelector('link[href*="bootstrap"]') !== null &&
          typeof window.bootstrap !== "undefined";
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
          body.textContent = guardMsg;
          toast.appendChild(body);
          container.appendChild(toast);
          bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(guardMsg);
        }
        f.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (error) {}
})();
