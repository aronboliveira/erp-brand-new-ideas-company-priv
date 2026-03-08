/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */

((): void => {
  try {
    const fm = document.querySelector(
      'form[id^="job-update-form-"][data-url][data-guard-msg]'
    );
    if (!fm) return;
    if (fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");
    fm.addEventListener("submit", (e: Event) => {
      try {
        const action = (fm.getAttribute("action") ?? "#").trim();
        const url = (fm.getAttribute("data-url") ?? "#").trim();
        if (url !== "#" && action !== "#") return;
        e.preventDefault();
        const msg =
          fm.getAttribute("data-guard-msg") ??
          "Job update route is unavailable. Please contact technical support or your domain administrator.";
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
          const t = document.createElement("div");
          t.className = "toast";
          for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  t.setAttribute(k, v);
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
      } catch (__err) {
    console.error(`[edit] Error:`, __err);
  }
    });
  } catch (__err) {
    console.error(`[edit] Error:`, __err);
  }
})();

((): void => {
  try {
    const anchors = document.querySelectorAll(
      "a.ai-btn[data-ajax-popup-over][data-url][data-guard-msg], a.grammar-btn[data-ajax-popup-over][data-url][data-guard-msg]"
    );
    if (anchors.length === 0) return;
    anchors.forEach(a => {
      if (a.getAttribute("data-click-guarded") === "true") return;
      a.setAttribute("data-click-guarded", "true");
      a.addEventListener("click", (e: Event) => {
        try {
          const href = (a.getAttribute("href") ?? "#").trim();
          const url = (a.getAttribute("data-url") ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg =
            a.getAttribute("data-guard-msg") ??
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
            const t = document.createElement("div");
            t.className = "toast";
            for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  t.setAttribute(k, v);
            const b = document.createElement("div");
            b.className = "toast-body";
            b.textContent = msg;
            t.appendChild(b);
            container.appendChild(t);
            bootstrap.Toast.getOrCreateInstance(t).show();
          } else {
            alert(msg);
          }
          a.setAttribute("data-failed-route", "true");
        } catch (__err) {
    console.error(`[edit] Error:`, __err);
  }
      });
    });
  } catch (__err) {
    console.error(`[edit] Error:`, __err);
  }
})();

export {};
