(() => {
  try {
    const sel = document.getElementById("language");
    if (!sel) return;
    if (sel.getAttribute("data-listener-active") === "true") return;
    sel.setAttribute("data-listener-active", "true");

    const guardMsg =
      sel.getAttribute("data-guard-msg") ??
      "Language switch route is unavailable. Please contact technical support or your domain administrator.";

    sel.addEventListener("change", e => {
      try {
        const val = sel.options?.[sel.selectedIndex]?.value ?? "#";
        if (val && val !== "#") return;

        e.preventDefault();

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
          body.textContent = guardMsg;

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
                "[assets/js/routes/auth/languageSwitcher.js] Bootstrap toast error:",
                err?.constructor?.name ?? "Error",
                err?.message ?? "Unknown error"
              );
            alert(guardMsg);
          }
        } else {
          alert(guardMsg);
        }

        sel.setAttribute("data-failed-route", "true");
      } catch (err) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error(
            "[assets/js/routes/auth/languageSwitcher.js] Change handler error:",
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
        "[assets/js/routes/auth/languageSwitcher.js] Initialization error:",
        error?.constructor?.name ?? "Error",
        error?.message ?? "Unknown error"
      );
  }
})();
