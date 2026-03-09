/**
 * @fileoverview TypeScript version of public/assets/js/routes/indicators/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

((): void => {
  try {
    const showGuard = (msg: string): void => {
      const text =
          msg ??
          "Requested route is unavailable. Please contact technical support or your domain administrator.",
        hasBootstrap = !!(
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
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          t.setAttribute(k, v);
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = text;
        t.appendChild(b);
        container.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(text);
      }
    };

    const bindLinkGuard = (a: Element): void => {
      if (!a || a.getAttribute("data-listener-active") === "true") return;
      a.setAttribute("data-listener-active", "true");
      a.addEventListener("click", (e: Event) => {
        try {
          const href = (a.getAttribute("href") ?? "#").trim(),
            url = (a.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          showGuard(a.getAttribute("data-guard-msg") ?? "");
          a.setAttribute("data-failed-route", "true");
        } catch (_) {
          console.error(`[index] Error:`, _);
        }
      });
    };

    const bindFormGuard = (fm: Element): void => {
      if (!fm || fm.getAttribute("data-submit-guarded") === "true") return;
      fm.setAttribute("data-submit-guarded", "true");
      if (!fm.getAttribute("data-listener-bound-submit")) {
        fm.setAttribute("data-listener-bound-submit", "1");
        fm.addEventListener("submit", (e: Event) => {
          try {
            const action = (fm.getAttribute("action") ?? "#").trim(),
              url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
            if (url !== "#" && action !== "#") return;
            e.preventDefault();
            showGuard(fm.getAttribute("data-guard-msg") ?? "");
            fm.setAttribute("data-failed-route", "true");
          } catch (_) {
            console.error(`[index] Error:`, _);
          }
        });
      }
    };

    document
      .querySelectorAll("a[data-guard-msg], a[data-url]")
      .forEach(bindLinkGuard);
    document
      .querySelectorAll("form[data-guard-msg], form[data-url]")
      .forEach(bindFormGuard);

    try {
      document
        .querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach((el: Element): void => {
          try {
            bootstrap.Tooltip.getOrCreateInstance(el);
          } catch (_) {
            console.error(`[index] Error:`, _);
          }
        });
    } catch (_) {
      console.error(`[index] Error:`, _);
    }
  } catch (_) {
    console.error(`[index] Error:`, _);
  }
})();

export {};
