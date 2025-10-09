(() => {
  try {
    const f = document.getElementById("update-project-stage-form");
    if (!f) return;
    const flag = "data-submit-listener";
    if (f.hasAttribute(flag) && f.getAttribute(flag) === "true") return;
    f.setAttribute(flag, "true");
    f.addEventListener(
      "submit",
      function (e) {
        try {
          const action = f.getAttribute("action") || "#";
          if (action !== "#") return;
          e.preventDefault();
          const msg =
            f.getAttribute("data-guard-msg") ||
            "Update project stage route is unavailable. Please contact technical support or your domain administrator.";
          const hasBootstrap =
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap &&
            window.bootstrap.Toast;
          let container = document.getElementById("toast-container");
          if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className =
              "toast-container position-fixed top-0 end-0 p-3";
            container.style.zIndex = "1080";
            container.className = "position-fixed top-0 end-0 p-3";
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
            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
            toast.addEventListener("hidden.bs.toast", function () {
              try {
                toast.remove();
              } catch (_) {}
            });
            inst.show();
          } else {
            alert(msg);
          }
          f.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false }
    );
  } catch (_) {}
})();
