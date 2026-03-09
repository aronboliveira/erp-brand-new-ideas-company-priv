(() => {
  try {
    const host = document.documentElement;
    const flag = "data-apply-income-vs-expense-summary-listener";
    if (host.hasAttribute(flag) && host.getAttribute(flag) === "true") return;
    host.setAttribute(flag, "true");
    document.addEventListener(
      "click",
      function (e) {
        try {
          const a =
            e.target &&
            (e.target.closest
              ? e.target.closest("a.apply-income-vs-expense-summary")
              : null);
          if (!a) return;
          e.preventDefault();
          const formId = a.getAttribute("data-form-id") || "";
          const f = formId ? document.getElementById(formId) : null;
          if (!f) return;
          const action = f.getAttribute("action") || "#";
          const url = f.getAttribute("data-url") || action || "#";
          if (url !== "#") {
            try {
              f.submit();
            } catch (_) {}
            return;
          }
          const msg =
            f.getAttribute("data-guard-msg") ||
            a.getAttribute("data-guard-msg") ||
            "Income vs expense summary report route is unavailable. Please contact technical support or your domain administrator.";
          const linkEl = document.querySelector('link[href*="bootstrap"]');
          const hasBootstrapToast =
            typeof window !== "undefined" &&
            window.bootstrap &&
            typeof window.bootstrap.Toast === "function";
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
          f.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false }
    );
  } catch (_) {}
})();
