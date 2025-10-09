(() => {
  try {
    const fm = document.getElementById("product-service-filter-form");
    if (!fm) {
      return;
    }

    // Guard form submission (e.g., user presses Enter)
    if (fm.getAttribute("data-submit-guarded") !== "true") {
      fm.setAttribute("data-submit-guarded", "true");
      fm.addEventListener("submit", e => {
        try {
          const action = (fm.getAttribute("action") ?? "#").trim();
          const url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
          if (url !== "#" && action !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            fm.getAttribute("data-guard-msg") ??
            "Product & Service index route is unavailable. Please contact technical support or your domain administrator.";
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
            const t = document.createElement("div");
            t.className = "toast";
            t.setAttribute("role", "alert");
            t.setAttribute("aria-live", "assertive");
            t.setAttribute("aria-atomic", "true");
            const b = document.createElement("div");
            b.className = "toast-body";
            b.textContent = msg;
            t.appendChild(b);
            container.appendChild(t);
            bootstrap.Toast.getOrCreateInstance(t).show();
          } else {
            alert(msg);
          }
          fm.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }

    // Apply button (submit)
    const applyBtn = document.getElementById("product-service-apply-btn");
    if (applyBtn && applyBtn.getAttribute("data-listener-active") !== "true") {
      applyBtn.setAttribute("data-listener-active", "true");
      applyBtn.addEventListener("click", e => {
        try {
          const href = (applyBtn.getAttribute("href") ?? "#").trim();
          const url = (applyBtn.getAttribute("data-url") ?? href ?? "#").trim();
          if (url === "#" || href === "#") {
            e.preventDefault();
            const msg =
              applyBtn.getAttribute("data-guard-msg") ??
              "Product & Service index route is unavailable. Please contact technical support or your domain administrator.";
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
              const t = document.createElement("div");
              t.className = "toast";
              t.setAttribute("role", "alert");
              t.setAttribute("aria-live", "assertive");
              t.setAttribute("aria-atomic", "true");
              const b = document.createElement("div");
              b.className = "toast-body";
              b.textContent = msg;
              t.appendChild(b);
              container.appendChild(t);
              bootstrap.Toast.getOrCreateInstance(t).show();
            } else {
              alert(msg);
            }
            applyBtn.setAttribute("data-failed-route", "true");
            return;
          }
          e.preventDefault();
          if (fm && typeof fm.submit === "function") {
            fm.submit();
          }
        } catch (err) {}
      });
    }

    // Reset link (navigate to index)
    const resetLink = document.getElementById("product-service-reset-link");
    if (
      resetLink &&
      resetLink.getAttribute("data-listener-active") !== "true"
    ) {
      resetLink.setAttribute("data-listener-active", "true");
      resetLink.addEventListener("click", e => {
        try {
          const href = (resetLink.getAttribute("href") ?? "#").trim();
          const url = (
            resetLink.getAttribute("data-url") ??
            href ??
            "#"
          ).trim();
          if (url !== "#" && href !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            resetLink.getAttribute("data-guard-msg") ??
            "Product & Service index route is unavailable. Please contact technical support or your domain administrator.";
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
            const t = document.createElement("div");
            t.className = "toast";
            t.setAttribute("role", "alert");
            t.setAttribute("aria-live", "assertive");
            t.setAttribute("aria-atomic", "true");
            const b = document.createElement("div");
            b.className = "toast-body";
            b.textContent = msg;
            t.appendChild(b);
            container.appendChild(t);
            bootstrap.Toast.getOrCreateInstance(t).show();
          } else {
            alert(msg);
          }
          resetLink.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }
  } catch (err) {}
})();
