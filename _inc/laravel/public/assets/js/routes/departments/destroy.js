(() => {
  try {
    const links = Array.from(
      document.querySelectorAll(
        'a[id^="delete-department-btn-"][data-url][data-guard-msg]'
      )
    );
    if (!links || !links.length) return;
    links.forEach(l => {
      if (l.getAttribute("data-click-guarded") === "true") return;
      l.setAttribute("data-click-guarded", "true");
      l.addEventListener("click", e => {
        try {
          const url = (l.getAttribute("data-url") ?? "#").trim();
          if (url !== "#") return;
          e.preventDefault();
          const msg =
            l.getAttribute("data-guard-msg") ??
            "Delete department route is unavailable. Please contact technical support or your domain administrator.";
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
            const t = document.createElement("div");
            t.className = "toast";
            t.setAttribute("role", "alert");
            t.setAttribute("aria-live", "assertive");
            t.setAttribute("aria-atomic", "true");
            const b = document.createElement("div");
            b.className = "toast-body";
            b.textContent = msg;
            t.appendChild(b);
            container.appendChild(t);
            bootstrap.Toast.getOrCreateInstance(t).show();
          } else {
            alert(msg);
          }
          l.setAttribute("data-failed-route", "true");
        } catch {}
      });
    });
  } catch {}
})();
