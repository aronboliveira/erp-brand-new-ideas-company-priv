(() => {
  try {
    const a = document.querySelector(".auth-login-link");
    if (!a) return;
    if (a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");

    const url = a.getAttribute("data-url") ?? "#";
    if (
      a.hasAttribute("href") &&
      (a.getAttribute("href") === "#" || !a.getAttribute("href")) &&
      url !== "#"
    ) {
      a.setAttribute("href", url);
    }

    a.addEventListener("click", e => {
      try {
        const href = a.getAttribute("href") ?? "#";
        if (href && href !== "#") return;
        e.preventDefault();

        const msg =
          a.getAttribute("data-guard-msg") ??
          "Login route is unavailable. Please contact technical support or your domain administrator.";

        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
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
                "[assets/js/routes/auth/loginLink.js] Bootstrap toast error:",
                err?.constructor?.name ?? "Error",
                err?.message ?? "Unknown error"
              );
            alert(msg);
          }
        } else {
          alert(msg);
        }

        a.setAttribute("data-failed-route", "true");
      } catch (err) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error(
            "[assets/js/routes/auth/loginLink.js] Click handler error:",
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
        "[assets/js/routes/auth/loginLink.js] Initialization error:",
        error?.constructor?.name ?? "Error",
        error?.message ?? "Unknown error"
      );
  }
})();
