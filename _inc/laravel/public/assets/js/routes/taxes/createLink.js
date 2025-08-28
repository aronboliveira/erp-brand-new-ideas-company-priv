(() => {
  try {
    const a = document.getElementById("tax-create-link");
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
          "Create tax route is unavailable. Please contact technical support or your domain administrator.";
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
            console.error(
              "[assets/js/routes/taxes/createLink.js] Bootstrap toast error:",
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
        console.error(
          "[assets/js/routes/taxes/createLink.js] Click handler error:",
          err?.constructor?.name ?? "Error",
          err?.message ?? "Unknown error"
        );
      }
    });
  } catch (error) {
    console.error(
      "[assets/js/routes/taxes/createLink.js] Initialization error:",
      error?.constructor?.name ?? "Error",
      error?.message ?? "Unknown error"
    );
  }
})();
