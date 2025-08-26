(() => {
  try {
    const links = document.querySelectorAll("a.project-show-link");
    if (!links || !links.length) {
      return;
    }
    links.forEach(el => {
      try {
        if (el.getAttribute("data-listener-active") === "true") {
          return;
        }
        el.setAttribute("data-listener-active", "true");
        el.addEventListener("click", e => {
          try {
            const href = el.getAttribute("href") ?? "#";
            const url = el.getAttribute("data-url") ?? href ?? "#";
            if (url !== "#" && href !== "#") {
              return;
            }
            e.preventDefault();
            const msg =
              el.getAttribute("data-guard-msg") ??
              "Show project route is unavailable. Please contact technical support or your domain administrator.";
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
            el.setAttribute("data-failed-route", "true");
          } catch (err) {}
        });
      } catch (err) {}
    });
  } catch (err) {}
})();
