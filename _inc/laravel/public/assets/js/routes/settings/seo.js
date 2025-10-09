(() => {
  try {
    const seoGen = document.getElementById("generate-ai-seo-link");
    if (seoGen && seoGen.getAttribute("data-listener-active") !== "true") {
      seoGen.setAttribute("data-listener-active", "true");
      seoGen.addEventListener("click", e => {
        try {
          const url = seoGen.getAttribute("data-url") || "#";
          if (url !== "#") return;
          e.preventDefault();
          const msg = seoGen.getAttribute("data-guard-msg") || "# ERROR";
          const hasBootstrap =
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap;
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
          seoGen.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }

    const cookieGen = document.getElementById("generate-ai-cookie-link");
    if (
      cookieGen &&
      cookieGen.getAttribute("data-listener-active") !== "true"
    ) {
      cookieGen.setAttribute("data-listener-active", "true");
      cookieGen.addEventListener("click", e => {
        try {
          const url = cookieGen.getAttribute("data-url") || "#";
          if (url !== "#") return;
          e.preventDefault();
          const msg = cookieGen.getAttribute("data-guard-msg") || "# ERROR";
          const hasBootstrap =
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap;
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
          cookieGen.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }

    const seoForm = document.getElementById("settings-seo-store-form");
    if (seoForm && seoForm.getAttribute("data-listener-active") !== "true") {
      seoForm.setAttribute("data-listener-active", "true");
      seoForm.addEventListener("submit", e => {
        try {
          const url = seoForm.getAttribute("data-url") || "#";
          const action = seoForm.getAttribute("action") || "#";
          if (url !== "#" || action !== "#") return;
          e.preventDefault();
          const msg = seoForm.getAttribute("data-guard-msg") || "# ERROR";
          const hasBootstrap =
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap;
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
          seoForm.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }

    const cookiesForm = document.getElementById(
      "{{ $settingsCookiesStoreFormId }}"
    );
    if (
      cookiesForm &&
      cookiesForm.getAttribute("data-listener-active") !== "true"
    ) {
      cookiesForm.setAttribute("data-listener-active", "true");
      cookiesForm.addEventListener("submit", e => {
        try {
          const url = cookiesForm.getAttribute("data-url") || "#";
          const action = cookiesForm.getAttribute("action") || "#";
          if (url !== "#" || action !== "#") return;
          e.preventDefault();
          const msg = cookiesForm.getAttribute("data-guard-msg") || "# ERROR";
          const hasBootstrap =
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap;
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
          cookiesForm.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }

    const chatForm = document.getElementById("settings-chatgpt-settings-form");
    if (chatForm && chatForm.getAttribute("data-listener-active") !== "true") {
      chatForm.setAttribute("data-listener-active", "true");
      chatForm.addEventListener("submit", e => {
        try {
          const url = chatForm.getAttribute("data-url") || "#";
          const action = chatForm.getAttribute("action") || "#";
          if (url !== "#" || action !== "#") return;
          e.preventDefault();
          const msg = chatForm.getAttribute("data-guard-msg") || "# ERROR";
          const hasBootstrap =
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap;
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
          chatForm.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }
  } catch (err) {}
})();
