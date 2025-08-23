(() => {
  try {
    const f = document.getElementById("edit_leave");
    if (!f) return;
    if (
      f.hasAttribute("data-submit-listener") &&
      f.getAttribute("data-submit-listener") === "true"
    )
      return;
    f.setAttribute("data-submit-listener", "true");
    const toast = msg => {
      try {
        const linkEl = document.querySelector('link[href*="bootstrap"]');
        const hasBootstrap =
          !!linkEl && window.bootstrap && window.bootstrap.Toast;
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className = "position-fixed top-0 end-0 p-3";
          document.body.appendChild(container);
        }
        if (hasBootstrap) {
          const t = document.createElement("div");
          t.className = "toast";
          t.setAttribute("role", "alert");
          t.setAttribute("aria-live", "assertive");
          t.setAttribute("aria-atomic", "true");
          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent =
            msg ||
            "Requested route is unavailable. Please contact technical support or your domain administrator.";
          t.appendChild(body);
          container.appendChild(t);
          const inst = window.bootstrap.Toast.getOrCreateInstance(t);
          t.addEventListener("hidden.bs.toast", function () {
            try {
              t.remove();
            } catch (e) {}
          });
          inst.show();
        } else {
          alert(
            msg ||
              "Requested route is unavailable. Please contact technical support or your domain administrator."
          );
        }
      } catch (e) {}
    };
    f.addEventListener(
      "submit",
      function (e) {
        try {
          const action = f.getAttribute("action") || "#";
          if (action !== "#") return;
          e.preventDefault();
          const msg =
            f.getAttribute("data-guard-msg") ||
            "Update leave route is unavailable. Please contact technical support or your domain administrator.";
          toast(msg);
          f.setAttribute("data-failed-route", "true");
        } catch (err) {}
      },
      { passive: false }
    );
  } catch (error) {}
})();
