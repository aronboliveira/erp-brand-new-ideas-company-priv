/**
 * @fileoverview TypeScript version of public/assets/js/routes/formBuilders/bindStore.js
 * @generated from original JavaScript - manual review recommended
 * @module bindStore
 */

/* global bootstrap */
((): void => {
  try {
    const fm = document.getElementById("fm-bind-store-form");
    if (fm && fm.getAttribute("data-submit-guarded") !== "true") {
      fm.setAttribute("data-submit-guarded", "true");
      fm.addEventListener("submit", (e: Event) => {
        try {
          const action = (fm.getAttribute("action") ?? "#").trim();
          const url = (fm.getAttribute("data-url") ?? "#").trim();
          if (url !== "#" && action !== "#") return;
          e.preventDefault();
          const msg =
            fm.getAttribute("data-guard-msg") ??
            "Form bind store route is unavailable. Please contact technical support or your domain administrator.";
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

    const radios = document.querySelectorAll(".lead_radio");
    const section = document.getElementById("lead_activated");
    const applyLeadToggle = (): void => {
      try {
        const on =
          (Array.from(radios) as HTMLInputElement[]).find(r => r.checked)
            ?.value === "1";
        if (!section) return;
        if (on) {
          section.classList.remove("d-none");
        } else {
          section.classList.add("d-none");
        }
      } catch (err) {}
    };
    if (radios.length !== 0) {
      radios.forEach(r => {
        r.addEventListener("change", applyLeadToggle);
      });
      applyLeadToggle();
    }

    const empLink = document.getElementById("employee-index-link");
    if (empLink && empLink.getAttribute("data-listener-active") !== "true") {
      empLink.setAttribute("data-listener-active", "true");
      empLink.addEventListener("click", (e: Event) => {
        try {
          const href = (empLink.getAttribute("href") ?? "#").trim();
          const url = (empLink.getAttribute("data-url") ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg =
            empLink.getAttribute("data-guard-msg") ??
            "Employee index route is unavailable. Please contact technical support or your domain administrator.";
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
          empLink.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }
  } catch (err) {}
})();

export {};
