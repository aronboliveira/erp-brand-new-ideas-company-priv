(() => {
  try {
    const links = document.querySelectorAll("a.support-edit");
    if (!links || !links.length) return;
    links.forEach(l => {
      try {
        if (l.getAttribute("data-listener-active") === "true") return;
        l.setAttribute("data-listener-active", "true");
        l.addEventListener("click", e => {
          try {
            const href = l.getAttribute("href") ?? "#";
            const url = l.getAttribute("data-url") ?? href ?? "#";
            if (url !== "#" && href !== "#") return;
            e.preventDefault();
            const msg =
              l.getAttribute("data-guard-msg") ??
              "Edit support route is unavailable. Please contact technical support or your domain administrator.";
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
            l.setAttribute("data-failed-route", "true");
          } catch (err) {}
        });
      } catch (err) {}
    });
  } catch (err) {}
})();
