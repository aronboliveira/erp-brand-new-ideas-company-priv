(() => {
  try {
    const fm = document.getElementById("loginForm");
    if (!fm) {
      return;
    }
    if (fm.getAttribute("data-listener-active") === "true") {
      return;
    }
    fm.setAttribute("data-listener-active", "true");
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
          "Login submit route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
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
    const pwd = document.getElementById("password-request-link");
    if (pwd && pwd.getAttribute("data-listener-active") !== "true") {
      pwd.setAttribute("data-listener-active", "true");
      pwd.addEventListener("click", e => {
        try {
          const href = pwd.getAttribute("href") ?? "#";
          const url = pwd.getAttribute("data-url") ?? href ?? "#";
          if (url !== "#" && href !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            pwd.getAttribute("data-guard-msg") ??
            "Password request route is unavailable. Please contact technical support or your domain administrator.";
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
          pwd.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }
    const reg = document.getElementById("register-link");
    if (reg && reg.getAttribute("data-listener-active") !== "true") {
      reg.setAttribute("data-listener-active", "true");
      reg.addEventListener("click", e => {
        try {
          const href = reg.getAttribute("href") ?? "#";
          const url = reg.getAttribute("data-url") ?? href ?? "#";
          if (url !== "#" && href !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            reg.getAttribute("data-guard-msg") ??
            "Register route is unavailable. Please contact technical support or your domain administrator.";
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
          reg.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }
  } catch (err) {}
})();
