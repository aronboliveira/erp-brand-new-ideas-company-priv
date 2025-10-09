(() => {
  try {
    const links = document.querySelectorAll(
      'a[data-ajax-popup-over="true"][data-url][data-guard-msg]'
    );
    if (!links || links.length === 0) return;
    links.forEach(l => {
      try {
        if (!l || l.getAttribute("data-listener-active") === "true") return;
        l.setAttribute("data-listener-active", "true");
        const url = l.getAttribute("data-url") || "#";
        const href = l.getAttribute("href") || "#";
        if (href === "#" && url !== "#") {
          l.setAttribute("href", url);
        }
        l.addEventListener("click", e => {
          try {
            const u =
              l.getAttribute("data-url") || l.getAttribute("href") || "#";
            if (u !== "#") return;
            e.preventDefault();
            const msgAttr = l.getAttribute("data-guard-msg") || "";
            const msg =
              msgAttr && msgAttr.trim().length
                ? msgAttr
                : "Generate transfer content route is unavailable. Please contact technical support or your domain administrator.";
            const bsLink = document.querySelector('link[href*="bootstrap"]');
            let container = document.getElementById("toast-container");
            if (!container) {
              container = document.createElement("div");
              container.id = "toast-container";
              container.className =
                "toast-container position-fixed top-0 end-0 p-3";
              container.style.zIndex = "1080";
              document.body.appendChild(container);
            }
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
            l.setAttribute("data-failed-route", "true");
          } catch (err) {}
        });
      } catch (innerErr) {}
    });
  } catch (error) {}
})();
