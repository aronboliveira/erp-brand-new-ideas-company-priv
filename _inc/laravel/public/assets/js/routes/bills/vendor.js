(() => {
  try {
    const s = document.getElementById("vendor");
    if (!s) return;
    const flag = "data-change-listener";
    if (s.hasAttribute(flag) && s.getAttribute(flag) === "true") return;
    s.setAttribute(flag, "true");
    s.addEventListener(
      "change",
      function (e) {
        try {
          const url = s.getAttribute("data-url") || "#";
          if (url !== "#") return;
          const msg =
            s.getAttribute("data-guard-msg") ||
            "Bill vendor route is unavailable. Please contact technical support or your domain administrator.";
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
          s.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: true }
    );
  } catch (_) {}
})();
