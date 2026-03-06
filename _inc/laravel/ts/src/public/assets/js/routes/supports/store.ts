/**
 * @fileoverview TypeScript version of public/assets/js/routes/supports/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  try {
    const f = document.getElementById("store-support-form");
    if (!f) return;
    if (f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") ?? "#";
    if (
      f.hasAttribute("action") &&
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      (f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
      resolved !== "#"
    ) {
      f.setAttribute("action", resolved);
    }

    f.addEventListener("submit", e => {
      try {
        const action = f.getAttribute("action") ?? "#";
        if (action !== "#") return;
        e.preventDefault();

        const msg =
          f.getAttribute("data-guard-msg") ??
          "Store support route is unavailable. Please contact technical support or your domain administrator.";
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
          // eslint-disable-next-line @typescript-eslint/prefer-optional-chain
          typeof window.bootstrap !== "undefined" &&
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
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
                "[assets/js/routes/supports/store.js] Bootstrap toast instantiation error:",
                err?.constructor?.name ?? "Error",
                err?.message ?? "Unknown error"
              );
            alert(msg);
          }
        } else {
          alert(msg);
        }

        f.setAttribute("data-failed-route", "true");
      } catch (err) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error(
            "[assets/js/routes/supports/store.js] Submit handler error:",
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
        "[assets/js/routes/supports/create.js] Initialization error:",
        error?.constructor?.name ?? "Error",
        error?.message ?? "Unknown error"
      );
  }
})();

export {};
