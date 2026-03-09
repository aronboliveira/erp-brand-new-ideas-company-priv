/**
 * @fileoverview TypeScript version of public/assets/js/routes/ai/generate/terminationEdit.js
 * @generated from original JavaScript - manual review recommended
 * @module terminationEdit
 */

((): void => {
  try {
    const a = document.getElementById("gen-ai-termination");
    if (!a) return;
    if (a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");

    const url = a.getAttribute("data-url") ?? "#";
    if (a.hasAttribute("href") && a.getAttribute("href") === "#" && url !== "#")
      a.setAttribute("href", url);

    a.addEventListener("click", (e: Event) => {
      try {
        const href = a.getAttribute("href") ?? "#";
        if (href !== "#") return;
        e.preventDefault();

        const msg =
          a.getAttribute("data-guard-msg") ??
          "Generate content route is unavailable. Please contact technical support or your domain administrator.";
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
          body.textContent = msg;
          toast.appendChild(body);
          container.appendChild(toast);
          window.bootstrap.Toast.getOrCreateInstance(toast).show();
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
            "[assets/js/routes/terminations/generateAi.js] Click handler error:",
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            err?.constructor?.name ?? "Error",
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            (err as Error)?.message ?? "Unknown error",
          );
      }
    });
  } catch (error) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error(
        "[assets/js/routes/terminations/generateAi.js] Initialization error:",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        error?.constructor?.name ?? "Error",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        (error as Error)?.message ?? "Unknown error",
      );
  }
})();

export {};
