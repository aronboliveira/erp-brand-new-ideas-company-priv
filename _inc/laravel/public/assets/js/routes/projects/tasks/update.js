(() => {
  try {
    const guardToast = msg => {
      try {
        const hasBootstrap =
          document.querySelector('link[href*="bootstrap"]') &&
          window.bootstrap &&
          window.bootstrap.Toast;
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className = "position-fixed top-0 end-0 p-3";
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
          body.textContent =
            msg ||
            "Requested route is unavailable. Please contact technical support or your domain administrator.";
          toast.appendChild(body);
          container.appendChild(toast);
          const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
          toast.addEventListener("hidden.bs.toast", function () {
            try {
              toast.remove();
            } catch (err) {}
          });
          inst.show();
        } else {
          alert(
            msg ||
              "Requested route is unavailable. Please contact technical support or your domain administrator."
          );
        }
      } catch (err) {}
    };

    const f = document.getElementById("edit_task");
    if (
      f &&
      !(
        f.hasAttribute("data-submit-listener") &&
        f.getAttribute("data-submit-listener") === "true"
      )
    ) {
      f.setAttribute("data-submit-listener", "true");
      f.addEventListener(
        "submit",
        function (e) {
          try {
            const action = f.getAttribute("action") || "#";
            if (action !== "#") return;
            e.preventDefault();
            const msg =
              f.getAttribute("data-guard-msg") ||
              "Update project task route is unavailable. Please contact technical support or your domain administrator.";
            guardToast(msg);
            f.setAttribute("data-failed-route", "true");
          } catch (err) {}
        },
        { passive: false }
      );
    }

    const ai = document.getElementById("project-task-ai-generate-link");
    if (
      ai &&
      !(
        ai.hasAttribute("data-ai-listener") &&
        ai.getAttribute("data-ai-listener") === "true"
      )
    ) {
      ai.setAttribute("data-ai-listener", "true");
      ai.addEventListener(
        "click",
        function (e) {
          try {
            const href = ai.getAttribute("href") || "#";
            const url = ai.getAttribute("data-url") || href || "#";
            if (href !== "#" || url !== "#") return;
            e.preventDefault();
            const msg =
              ai.getAttribute("data-guard-msg") ||
              "Generate project task content route is unavailable. Please contact technical support or your domain administrator.";
            guardToast(msg);
            ai.setAttribute("data-failed-route", "true");
          } catch (err) {}
        },
        { passive: false }
      );
    }
  } catch (error) {}
})();
