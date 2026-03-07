/**
 * @fileoverview TypeScript version of public/assets/js/routes/goals/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

/* global bootstrap */
((): void => {
  try {
    const showGuard = (msg: string): void=> {
      const text =
        msg ??
        "Requested route is unavailable. Please contact technical support or your domain administrator.";
      const hasBootstrap = !!(
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap
      );
      let c = document.getElementById("toast-container");
      if (!c) {
        c = document.createElement("div");
        c.id = "toast-container";
        document.body.appendChild(c);
      }
      if (hasBootstrap) {
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = text;
        t.appendChild(b);
        c.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(text);
      }
    };

    const bindLinkGuard = (a: Element): void=> {
      if (!a || a.getAttribute("data-listener-active") === "true") return;
      a.setAttribute("data-listener-active", "true");
      a.addEventListener("click", (e: Event) => {
        try {
          const href = (a.getAttribute("href") ?? "#").trim();
          const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          showGuard(a.getAttribute("data-guard-msg") ?? "");
          a.setAttribute("data-failed-route", "true");
        } catch (_) {}
      });
    };

    const bindFormGuard = (fm: Element): void=> {
      if (!fm || fm.getAttribute("data-submit-guarded") === "true") return;
      fm.setAttribute("data-submit-guarded", "true");
      fm.addEventListener("submit", (e: Event) => {
        try {
          const action = (fm.getAttribute("action") ?? "#").trim();
          const url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
          if (url !== "#" && action !== "#") return;
          e.preventDefault();
          showGuard(fm.getAttribute("data-guard-msg") ?? "");
          fm.setAttribute("data-failed-route", "true");
        } catch (_) {}
      });
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
          } catch (_) {}
        });
    } catch (_) {}
  } catch (_) {}
})();

export {};
