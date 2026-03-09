/**
 * @fileoverview TypeScript version of public/assets/js/routes/auth/languageSwitcher.js
 * @generated from original JavaScript - manual review recommended
 * @module languageSwitcher
 */

((): void => {
  try {
    const sel = document.getElementById("language");
    if (!sel) return;
    if (sel.getAttribute("data-listener-active") === "true") return;
    sel.setAttribute("data-listener-active", "true");

    const guardMsg =
      sel.getAttribute("data-guard-msg") ??
      "Language switch route is unavailable. Please contact technical support or your domain administrator.";

    if (!sel.getAttribute("data-listener-bound-change")) {
      sel.setAttribute("data-listener-bound-change", "1");
      sel.addEventListener("change", (e: Event) => {
        try {
          const val =
            (sel as HTMLSelectElement).options[
              (sel as HTMLSelectElement).selectedIndex
            ].value ?? "#";
          if (val && val !== "#") return;

          e.preventDefault();

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
          if (bsLink && window.bootstrap.Toast) {
            const toast = document.createElement("div");
            toast.className = "toast";
            for (const [k, v] of Object.entries({
              role: "alert",
              "aria-live": "assertive",
              "aria-atomic": "true",
            }))
              toast.setAttribute(k, v);

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
                  // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                  err?.constructor?.name ?? "Error",
                  // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                  (err as Error)?.message ?? "Unknown error",
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
              // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
              err?.constructor?.name ?? "Error",
              // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
              (err as Error)?.message ?? "Unknown error",
            );
        }
      });
    }
  } catch (error) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error(
        "[assets/js/routes/auth/languageSwitcher.js] Initialization error:",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        error?.constructor?.name ?? "Error",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        (error as Error)?.message ?? "Unknown error",
      );
  }
})();

export {};
