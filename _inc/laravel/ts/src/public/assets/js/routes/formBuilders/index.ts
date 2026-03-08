/**
 * @fileoverview TypeScript version of public/assets/js/routes/formBuilders/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

((): void => {
  try {
    const showGuard = (msg: string): void=> {
      const hasBootstrap = !!(
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap
      );
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
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
        b.textContent =
          msg ??
          "Requested route is unavailable. Please contact technical support or your domain administrator.";
        t.appendChild(b);
        container.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(
          msg ??
            "Requested route is unavailable. Please contact technical support or your domain administrator.",
        );
      }
    };
    const bindGuard = (a: Element | null): void=> {
      if (!a || a.getAttribute("data-listener-active") === "true") return;
      a.setAttribute("data-listener-active", "true");
      a.addEventListener("click", (e: Event) => {
        try {
          const href = (a.getAttribute("href") ?? "#").trim();
          const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg =
            a.getAttribute("data-guard-msg") ??
            "Requested route is unavailable. Please contact technical support or your domain administrator.";
          showGuard(msg);
          a.setAttribute("data-failed-route", "true");
        } catch (err) {
    console.error(`[index] Error:`, err);
  }
      });
    };
    document
      .querySelectorAll("a[data-guard-msg], a[data-url]")
      .forEach(bindGuard);
  } catch (err) {
    console.error(`[index] Error:`, err);
  }
})();

export {};
