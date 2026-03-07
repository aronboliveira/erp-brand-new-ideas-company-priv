/**
 * @fileoverview TypeScript version of public/assets/js/routes/bank/transfers/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

/* global bootstrap */
((): void => {
  try {
    const fm = document.getElementById("transfer_form");
    if (fm && fm.getAttribute("data-submit-guarded") !== "true") {
      fm.setAttribute("data-submit-guarded", "true");
      fm.addEventListener("submit", (e: Event) => {
        try {
          const action = fm.getAttribute("action") ?? "#";
          const url = fm.getAttribute("data-url") ?? "#";
          if (url !== "#" && action !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            fm.getAttribute("data-guard-msg") ??
            "Apply bank transfer route is unavailable. Please contact technical support or your domain administrator.";
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
    }
    const apply = document.getElementById("transfer-apply");
    if (apply && apply.getAttribute("data-listener-active") !== "true") {
      apply.setAttribute("data-listener-active", "true");
      apply.addEventListener("click", (e: Event) => {
        try {
          e.preventDefault();
          const fid = apply.getAttribute("data-form-id") ?? "";
          if (fid === "") {
            return;
          }
          const form = document.getElementById(fid);
          if (!form) {
            return;
          }
          const action = form.getAttribute("action") ?? "#";
          const url = form.getAttribute("data-url") ?? "#";
          if (url === "#" || action === "#") {
            const msg =
              apply.getAttribute("data-guard-msg") ??
              "Apply bank transfer route is unavailable. Please contact technical support or your domain administrator.";
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
            apply.setAttribute("data-failed-route", "true");
            form.setAttribute("data-failed-route", "true");
            return;
          }
          (form as HTMLFormElement).submit();
        } catch (err) {}
      });
    }
    const reset = document.getElementById("transfer-reset");
    if (reset && reset.getAttribute("data-listener-active") !== "true") {
      reset.setAttribute("data-listener-active", "true");
      reset.addEventListener("click", (e: Event) => {
        try {
          const href = reset.getAttribute("href") ?? "#";
          const url = reset.getAttribute("data-url") ?? "#";
          if (url !== "#" && href !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            reset.getAttribute("data-guard-msg") ??
            "Reset bank transfer route is unavailable. Please contact technical support or your domain administrator.";
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
          reset.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }
  } catch (err) {}
})();

export {};
