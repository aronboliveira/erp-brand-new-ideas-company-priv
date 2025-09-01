(() => {
  try {
    const a = document.getElementById("zoom-ai-generate-link");
    if (!a || a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");
    const url = a.getAttribute("data-url") ?? "#";
    if (
      a.hasAttribute("href") &&
      (a.getAttribute("href") === "#" || !a.getAttribute("href")) &&
      url !== "#"
    ) {
      a.setAttribute("href", url);
    }
    a.addEventListener("click", e => {
      try {
        const href = a.getAttribute("href") ?? "#";
        if (href && href !== "#") return;
        e.preventDefault();
        const msg =
          a.getAttribute("data-guard-msg") ??
          "AI generation route is unavailable. Please contact technical support or your domain administrator.";
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          document.body.appendChild(container);
        }
        const hasBs =
          typeof window.bootstrap !== "undefined" && window.bootstrap?.Toast;
        if (hasBs) {
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
          try {
            window.bootstrap.Toast.getOrCreateInstance(toast).show();
          } catch {
            alert(msg);
          }
        } else {
          alert(msg);
        }
        a.setAttribute("data-failed-route", "true");
      } catch {}
    });
  } catch {}
})();
