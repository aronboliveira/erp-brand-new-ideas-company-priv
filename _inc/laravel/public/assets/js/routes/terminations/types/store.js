(() => {
  try {
    const formId = "termination-type-create-form";
    const f = document.getElementById(formId);
    if (!f || f.getAttribute("data-listener-active") === "true") {
      return;
    }
    f.setAttribute("data-listener-active", "true");

    f.addEventListener("submit", e => {
      try {
        const action = (f.getAttribute("action") ?? "").trim() || "#";
        const url = (f.getAttribute("data-url") ?? "").trim() || action || "#";
        if (action !== "#" || url !== "#") {
          return;
        }

        e.preventDefault();

        const msg =
          f.getAttribute("data-guard-msg") ||
          "Create termination type route is unavailable. Please contact technical support or your domain administrator.";
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          document.body.appendChild(container);
        }

        const hasBootstrapCss = !!document.querySelector(
          'link[href*="bootstrap"]'
        );
        const hasBootstrapJs = typeof window.bootstrap !== "undefined";
        if (hasBootstrapCss && hasBootstrapJs) {
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
          } catch (err) {
            alert(msg);
          }
        } else {
          alert(msg);
        }

        f.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (error) {}
})();
