// assets/js/routes/leaves/create.js
(() => {
  try {
    const l = document.getElementById("leave-create-link");
    if (!l) return;
    if (l.getAttribute("data-listener-active") === "true") return;
    l.setAttribute("data-listener-active", "true");
    l.addEventListener(
      "click",
      function (e) {
        try {
          const href = l.getAttribute("href") || "#";
          const url = l.getAttribute("data-url") || href || "#";
          if (href !== "#" || url !== "#") return;
          e.preventDefault();
          const msg =
            l.getAttribute("data-guard-msg") ||
            "Create leave route is unavailable. Please contact technical support or your domain administrator.";
          const linkEl = document.querySelector('link[href*="bootstrap"]');
          const hasBs =
            linkEl !== null && window.bootstrap && window.bootstrap.Toast;
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
          if (hasBs) {
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
            const inst = window.bootstrap.Toast.getOrCreateInstance(t);
            t.addEventListener("hidden.bs.toast", function () {
              try {
                t.remove();
              } catch (_) {}
            });
            inst.show();
          } else {
            alert(msg);
          }
          l.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false }
    );
  } catch (_) {}
})();
