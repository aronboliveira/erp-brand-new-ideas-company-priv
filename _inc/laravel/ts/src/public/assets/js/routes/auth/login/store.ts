/**
 * @fileoverview TypeScript version of public/assets/js/routes/auth/login/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

((): void => {
  try {
    const fm = document.getElementById("loginForm");
    if (!fm) {
      return;
    }
    if (fm.getAttribute("data-listener-active") === "true") {
      return;
    }
    fm.setAttribute("data-listener-active", "true");
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
          "Login submit route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
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
          for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toast.setAttribute(k, v);
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
      } catch (err) {
    console.error(`[store] Error:`, err);
  }
    });
    const pwd = document.getElementById("password-request-link");
    if (pwd && pwd.getAttribute("data-listener-active") !== "true") {
      pwd.setAttribute("data-listener-active", "true");
      pwd.addEventListener("click", (e: Event) => {
        try {
          const href = pwd.getAttribute("href") ?? "#";
          const url = pwd.getAttribute("data-url") ?? "#";
          if (url !== "#" && href !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            pwd.getAttribute("data-guard-msg") ??
            "Password request route is unavailable. Please contact technical support or your domain administrator.";
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
            for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toast.setAttribute(k, v);
            const body = document.createElement("div");
            body.className = "toast-body";
            body.textContent = msg;
            toast.appendChild(body);
            container.appendChild(toast);
            bootstrap.Toast.getOrCreateInstance(toast).show();
          } else {
            alert(msg);
          }
          pwd.setAttribute("data-failed-route", "true");
        } catch (err) {
    console.error(`[store] Error:`, err);
  }
      });
    }
    const reg = document.getElementById("register-link");
    if (reg && reg.getAttribute("data-listener-active") !== "true") {
      reg.setAttribute("data-listener-active", "true");
      reg.addEventListener("click", (e: Event) => {
        try {
          const href = reg.getAttribute("href") ?? "#";
          const url = reg.getAttribute("data-url") ?? "#";
          if (url !== "#" && href !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            reg.getAttribute("data-guard-msg") ??
            "Register route is unavailable. Please contact technical support or your domain administrator.";
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
            for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toast.setAttribute(k, v);
            const body = document.createElement("div");
            body.className = "toast-body";
            body.textContent = msg;
            toast.appendChild(body);
            container.appendChild(toast);
            bootstrap.Toast.getOrCreateInstance(toast).show();
          } else {
            alert(msg);
          }
          reg.setAttribute("data-failed-route", "true");
        } catch (err) {
    console.error(`[store] Error:`, err);
  }
      });
    }
  } catch (err) {
    console.error(`[store] Error:`, err);
  }
})();

export {};
