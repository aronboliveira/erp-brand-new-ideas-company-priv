(() => {
  try {
    const fm = document.getElementById("transfer_form");
    if (fm && fm.getAttribute("data-submit-guarded") !== "true") {
      fm.setAttribute("data-submit-guarded", "true");
      fm.addEventListener("submit", e => {
        try {
          const action = fm.getAttribute("action") ?? "#";
          const url = fm.getAttribute("data-url") ?? action ?? "#";
          if (url !== "#" && action !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            fm.getAttribute("data-guard-msg") ??
            "Apply bank transfer route is unavailable. Please contact technical support or your domain administrator.";
          const hasBootstrap = !!(
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap
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
          fm.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }
    const apply = document.getElementById("transfer-apply");
    if (apply && apply.getAttribute("data-listener-active") !== "true") {
      apply.setAttribute("data-listener-active", "true");
      apply.addEventListener("click", e => {
        try {
          e.preventDefault();
          const fid = apply.getAttribute("data-form-id") ?? "";
          if (!fid) {
            return;
          }
          const form = document.getElementById(fid);
          if (!form) {
            return;
          }
          const action = form.getAttribute("action") ?? "#";
          const url = form.getAttribute("data-url") ?? action ?? "#";
          if (url === "#" || action === "#") {
            const msg =
              apply.getAttribute("data-guard-msg") ??
              "Apply bank transfer route is unavailable. Please contact technical support or your domain administrator.";
            const hasBootstrap = !!(
              document.querySelector('link[href*="bootstrap"]') &&
              window.bootstrap
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
            apply.setAttribute("data-failed-route", "true");
            form.setAttribute("data-failed-route", "true");
            return;
          }
          form.submit();
        } catch (err) {}
      });
    }
    const reset = document.getElementById("transfer-reset");
    if (reset && reset.getAttribute("data-listener-active") !== "true") {
      reset.setAttribute("data-listener-active", "true");
      reset.addEventListener("click", e => {
        try {
          const href = reset.getAttribute("href") ?? "#";
          const url = reset.getAttribute("data-url") ?? href ?? "#";
          if (url !== "#" && href !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            reset.getAttribute("data-guard-msg") ??
            "Reset bank transfer route is unavailable. Please contact technical support or your domain administrator.";
          const hasBootstrap = !!(
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap
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
          reset.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }
  } catch (err) {}
})();
