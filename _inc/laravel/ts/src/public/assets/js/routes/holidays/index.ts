/**
 * @fileoverview TypeScript version of public/assets/js/routes/holidays/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

((): void => {
  try {
    const showGuard = (msg: string): void => {
      const text =
          msg ??
          "Requested route is unavailable. Please contact technical support or your domain administrator.",
        hasBs = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
        );
      let box = document.getElementById("toast-container");
      if (!box) {
        box = document.createElement("div");
        box.id = "toast-container";
        document.body.appendChild(box);
      }
      if (hasBs) {
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
        box.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(text);
      }
    };

    const bindLinkGuard = (el: HTMLElement | null): void => {
      if (!el || el.getAttribute("data-listener-active") === "true") return;
      el.setAttribute("data-listener-active", "true");
      if (!el.getAttribute("data-listener-bound-click")) {
        el.setAttribute("data-listener-bound-click", "1");
        el.addEventListener("click", (e: Event) => {
          try {
            const href = (el.getAttribute("href") ?? "#").trim(),
              url = (el.getAttribute("data-url") ?? href ?? "#").trim();
            if (url !== "#" && href !== "#") return;
            e.preventDefault();
            showGuard(el.getAttribute("data-guard-msg") ?? "");
            el.setAttribute("data-failed-route", "true");
          } catch (_) {
            console.error(`[index] Error:`, _);
          }
        });
      }
    };

    const bindFormGuard = (fm: Element | null): void => {
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
      .forEach(el => bindLinkGuard(el as HTMLElement));
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
