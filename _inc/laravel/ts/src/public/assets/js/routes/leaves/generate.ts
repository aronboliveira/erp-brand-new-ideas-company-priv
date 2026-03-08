/**
 * @fileoverview TypeScript version of public/assets/js/routes/leaves/generate.js
 * @generated from original JavaScript - manual review recommended
 * @module generate
 */

// assets/js/routes/leaves/generate.js
((): void => {
  try {
    const l = document.getElementById("leave-ai-generate-link");
    if (!l) return;
    if (
      l.hasAttribute("data-ai-listener") &&
      l.getAttribute("data-ai-listener") === "true"
    )
      return;
    l.setAttribute("data-ai-listener", "true");
    const toast = (msg: string): void=> {
      try {
        const linkEl = document.querySelector('link[href*="bootstrap"]');
        const hasBootstrap =
          !!linkEl && window.bootstrap.Toast;
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          container.className = "position-fixed top-0 end-0 p-3";
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
          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent =
            msg ?? "Requested route is unavailable. Please contact technical support or your domain administrator.";
          t.appendChild(body);
          container.appendChild(t);
          const inst = window.bootstrap.Toast.getOrCreateInstance(t);
          t.addEventListener("hidden.bs.toast", function (): void {
            try {
              t.remove();
            } catch (e) {
    console.error(`[generate] Error:`, e);
  }
          });
          inst.show();
        } else {
          alert(
            msg ?? "Requested route is unavailable. Please contact technical support or your domain administrator."
          );
        }
      } catch (e) {
    console.error(`[generate] Error:`, e);
  }
    };
    l.addEventListener(
      "click",
      function (e: Event) {
        try {
          const href = l.getAttribute("href") ?? "#";
          const url = l.getAttribute("data-url") ?? "#";
          if (href !== "#" || url !== "#") return;
          e.preventDefault();
          const msg =
            l.getAttribute("data-guard-msg") ?? "Generate leave content route is unavailable. Please contact technical support or your domain administrator.";
          toast(msg);
          l.setAttribute("data-failed-route", "true");
        } catch (err) {
    console.error(`[generate] Error:`, err);
  }
      },
      { passive: false }
    );
  } catch (error) {
    console.error(`[generate] Error:`, error);
  }
})();
