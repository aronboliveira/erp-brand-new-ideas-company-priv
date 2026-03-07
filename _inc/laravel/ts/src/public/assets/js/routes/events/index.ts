/**
 * @fileoverview TypeScript version of public/assets/js/routes/events/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

/* global bootstrap */
((): void => {
  try {
    const bindGuard = (el: Element | null): void=> {
      try {
        if (!el) {
          return;
        }
        if (el.getAttribute("data-listener-active") === "true") {
          return;
        }
        el.setAttribute("data-listener-active", "true");
        el.addEventListener("click", (e: Event) => {
          try {
            const href = el.getAttribute("href") ?? "#";
            const url = el.getAttribute("data-url") ?? href ?? "#";
            if (url !== "#" && href !== "#") {
              return;
            }
            e.preventDefault();
            const msg =
              el.getAttribute("data-guard-msg") ??
              "Requested route is unavailable. Please contact technical support or your domain administrator.";
            const hasBootstrap = !!(
              document.querySelector('link[href*="bootstrap"]') &&
              window.bootstrap
            );
            let container = document.getElementById("toast-container");
            if (!container) {
              container = document.createElement("div");
              container.id = "toast-container";
              container.className =
                "toast-container position-fixed top-0 end-0 p-3";
              container.style.zIndex = "1080";
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
              body.textContent = msg;
              toast.appendChild(body);
              container.appendChild(toast);
              bootstrap.Toast.getOrCreateInstance(toast).show();
            } else {
              alert(msg);
            }
            el.setAttribute("data-failed-route", "true");
          } catch (err) {}
        });
      } catch (err) {}
    };

    const bindFormGuard = (fm: Element): void=> {
      try {
        if (!fm) {
          return;
        }
        if (fm.getAttribute("data-submit-guarded") === "true") {
          return;
        }
        fm.setAttribute("data-submit-guarded", "true");
        fm.addEventListener("submit", (e: Event) => {
          try {
            const action = fm.getAttribute("action") ?? "#";
            const url = fm.getAttribute("data-url") ?? action ?? "#";
            if (url !== "#" && action !== "#") {
              return;
            }
            e.preventDefault();
            const msg =
              fm.getAttribute("data-guard-msg") ??
              "Requested route is unavailable. Please contact technical support or your domain administrator.";
            const hasBootstrap = !!(
              document.querySelector('link[href*="bootstrap"]') &&
              window.bootstrap
            );
            let container = document.getElementById("toast-container");
            if (!container) {
              container = document.createElement("div");
              container.id = "toast-container";
              container.className =
                "toast-container position-fixed top-0 end-0 p-3";
              container.style.zIndex = "1080";
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
              body.textContent = msg;
              toast.appendChild(body);
              container.appendChild(toast);
              bootstrap.Toast.getOrCreateInstance(toast).show();
            } else {
              alert(msg);
            }
            fm.setAttribute("data-failed-route", "true");
          } catch (err) {}
        });
      } catch (err) {}
    };

    bindGuard(document.getElementById("events-create-link"));

    document
      .querySelectorAll("[id^='events-edit-title-link-']")
      .forEach(bindGuard);
    document
      .querySelectorAll("[id^='events-edit-icon-link-']")
      .forEach(bindGuard);
    document.querySelectorAll("[id^='events-delete-link-']").forEach(bindGuard);
    document
      .querySelectorAll("form[id^='events-delete-form-']")
      .forEach(bindFormGuard);
  } catch (err) {}
})();

export {};
