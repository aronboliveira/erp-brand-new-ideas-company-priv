(() => {
  try {
    const f = document.getElementById("transaction_report");
    if (f && f.getAttribute("data-listener-active") !== "true") {
      f.setAttribute("data-listener-active", "true");
      const resolved = f.getAttribute("data-resolved-action") || "#";
      if (
        f.hasAttribute("action") &&
        (f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
        resolved !== "#"
      ) {
        f.setAttribute("action", resolved);
      }
      f.addEventListener("submit", e => {
        try {
          const action = f.getAttribute("action") || "#";
          if (action !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            f.getAttribute("data-guard-msg") ||
            "Transaction index route is unavailable. Please contact technical support or your domain administrator.";
          let container = document.getElementById("toast-container");
          if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className =
              "toast-container position-fixed top-0 end-0 p-3";
            container.style.zIndex = "1080";
            document.body.appendChild(container);
          }
          const bsLink = document.querySelector('link[href*="bootstrap"]');
          if (bsLink && typeof window.bootstrap !== "undefined") {
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
            window.bootstrap.Toast.getOrCreateInstance(toast).show();
          } else {
            alert(msg);
          }
          f.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }

    const reset = document.getElementById("transaction-report-reset");
    if (reset && reset.getAttribute("data-listener-active") !== "true") {
      reset.setAttribute("data-listener-active", "true");
      const url = reset.getAttribute("data-url") || "#";
      if (
        reset.hasAttribute("href") &&
        (reset.getAttribute("href") === "#" || !reset.getAttribute("href")) &&
        url !== "#"
      ) {
        reset.setAttribute("href", url);
      }
      reset.addEventListener("click", e => {
        try {
          const href = reset.getAttribute("href") || "#";
          if (href !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            reset.getAttribute("data-guard-msg") ||
            "Transaction index route is unavailable. Please contact technical support or your domain administrator.";
          let container = document.getElementById("toast-container");
          if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className =
              "toast-container position-fixed top-0 end-0 p-3";
            container.style.zIndex = "1080";
            document.body.appendChild(container);
          }
          const bsLink = document.querySelector('link[href*="bootstrap"]');
          if (bsLink && typeof window.bootstrap !== "undefined") {
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
            window.bootstrap.Toast.getOrCreateInstance(toast).show();
          } else {
            alert(msg);
          }
          reset.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }
  } catch (error) {}
})();
