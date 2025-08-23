(() => {
  try {
    const b = document.getElementById("file_attachment_submit");
    if (!b) return;
    const flag = "data-click-listener";
    if (b.hasAttribute(flag) && b.getAttribute(flag) === "true") return;
    b.setAttribute(flag, "true");
    b.addEventListener(
      "click",
      function (e) {
        try {
          const url =
            b.getAttribute("data-url") ?? b.getAttribute("data-action") ?? "#";
          if (url !== "#") return;
          e.preventDefault();
          const msg =
            b.getAttribute("data-guard-msg") ??
            "Store file for task comment route is unavailable. Please contact technical support or your domain administrator.";
          const linkEl = document.querySelector('link[href*="bootstrap"]');
          const hasBootstrapToast =
            typeof window !== "undefined" &&
            window.bootstrap &&
            typeof window.bootstrap.Toast === "function";
          let container = document.getElementById("toast-container");
          if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className = "position-fixed top-0 end-0 p-3";
            document.body.appendChild(container);
          }
          if (linkEl && hasBootstrapToast) {
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
          b.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false }
    );
  } catch (_) {}
})();
