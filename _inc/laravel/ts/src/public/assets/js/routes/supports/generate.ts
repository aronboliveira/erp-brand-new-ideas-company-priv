/**
 * @fileoverview TypeScript version of public/assets/js/routes/supports/generate.js
 * @generated from original JavaScript - manual review recommended
 * @module generate
 */


((): void => {
  try {
    const a = document.getElementById("support-generate-ai");
    if (!a) return;
    if (a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");

    a.addEventListener("click", (e: Event) => {
      try {
        const href = a.getAttribute("href") ?? "#";
        const url = a.getAttribute("data-url") ?? "#";
        if ((href && href !== "#") || (url && url !== "#")) return;
        e.preventDefault();

        const msg =
          a.getAttribute("data-guard-msg") ??
          "Generate support content route is unavailable. Please contact technical support or your domain administrator.";
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
          window.bootstrap.Toast
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
                "[assets/js/routes/supports/generate.js] Bootstrap toast instantiation error:",
                // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                err?.constructor?.name ?? "Error",
                // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
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
            "[assets/js/routes/supports/generate.js] Click handler error:",
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            err?.constructor?.name ?? "Error",
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
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
        "[assets/js/routes/supports/generate.js] Initialization error:",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        error?.constructor?.name ?? "Error",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        error?.message ?? "Unknown error"
      );
  }
})();

export {};
