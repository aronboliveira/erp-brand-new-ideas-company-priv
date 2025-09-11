(() => {
  try {
    const showGuard = msg => {
      const hasBootstrap = !!(
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap
      );
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        document.body.appendChild(container);
      }
      const text =
        msg ||
        "Requested route is unavailable. Please contact technical support or your domain administrator.";
      if (hasBootstrap) {
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = text;
        t.appendChild(b);
        container.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(text);
      }
    };

    const bindGuard = a => {
      if (!a || a.getAttribute("data-listener-active") === "true") return;
      a.setAttribute("data-listener-active", "true");
      a.addEventListener("click", e => {
        try {
          const href = (a.getAttribute("href") ?? "#").trim();
          const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg = a.getAttribute("data-guard-msg") ?? "";
          showGuard(msg);
          a.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    };

    document
      .querySelectorAll("a[data-guard-msg], a[data-url]")
      .forEach(bindGuard);

    try {
      const els = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
      );
      els.forEach(el => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch (e) {}
      });
    } catch (err) {}
  } catch (err) {}
})();
