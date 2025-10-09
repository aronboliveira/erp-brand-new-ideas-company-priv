(() => {
  try {
    const links = document.querySelectorAll(".project-task-index-link");
    if (!links || links.length === 0) return;

    links.forEach(l => {
      try {
        if (!l || l.getAttribute("data-listener-active") === "true") return;
        l.setAttribute("data-listener-active", "true");

        const url = l.getAttribute("data-url") ?? "#";
        if (
          l.hasAttribute("href") &&
          (l.getAttribute("href") === "#" || !l.getAttribute("href")) &&
          url !== "#"
        ) {
          l.setAttribute("href", url);
        }

        l.addEventListener("click", e => {
          try {
            const href = l.getAttribute("href") ?? "#";
            if (href && href !== "#") return;
            e.preventDefault();

            const msg =
              l.getAttribute("data-guard-msg") ??
              "Show project task route is unavailable. Please contact technical support or your domain administrator.";
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
            if (
              bsLink &&
              typeof window.bootstrap !== "undefined" &&
              window.bootstrap?.Toast
            ) {
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

              try {
                window.bootstrap.Toast.getOrCreateInstance(toast).show();
              } catch (err) {
                if (
                  window.location.hostname === "localhost" ||
                  window.location.hostname === "127.0.0.1"
                )
                  console.error(
                    "[assets/js/routes/projects/tasks/indexTable.js] Bootstrap toast instantiation error:",
                    err?.constructor?.name ?? "Error",
                    err?.message ?? "Unknown error"
                  );
                alert(msg);
              }
            } else {
              alert(msg);
            }

            l.setAttribute("data-failed-route", "true");
          } catch (err) {
            if (
              window.location.hostname === "localhost" ||
              window.location.hostname === "127.0.0.1"
            )
              console.error(
                "[assets/js/routes/projects/tasks/indexTable.js] Click handler error:",
                err?.constructor?.name ?? "Error",
                err?.message ?? "Unknown error"
              );
          }
        });
      } catch (err) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error(
            "[assets/js/routes/projects/tasks/indexTable.js] Link binding error:",
            err?.constructor?.name ?? "Error",
            err?.message ?? "Unknown error"
          );
      }
    });
  } catch (error) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error(
        "[assets/js/routes/projects/tasks/indexTable.js] Initialization error:",
        error?.constructor?.name ?? "Error",
        error?.message ?? "Unknown error"
      );
  }
})();
