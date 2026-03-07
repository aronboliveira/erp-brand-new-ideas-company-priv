/**
 * @fileoverview TypeScript version of public/assets/js/routes/estimations/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

/* global bootstrap */
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  try {
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const once = (el: HTMLElement, attr: string) => {
      if (!el) return false;
      if (el.getAttribute(attr) === "true") return false;
      el.setAttribute(attr, "true");
      return true;
    };

    const toast = (msg: string): void=> {
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
    };

    const guardClick = (el: HTMLElement | null): void=> {
      if (!el) return;
      if (!once(el, "data-listener-active")) return;
      el.addEventListener("click", (e: Event) => {
        try {
          const href = (el.getAttribute("href") ?? "#").trim();
          const url = (el.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg =
            el.getAttribute("data-guard-msg") ??
            "Route is unavailable. Please contact technical support or your domain administrator.";
          toast(msg);
          el.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    };

    guardClick(document.getElementById("est-create-btn"));
    document.querySelectorAll("a[data-guard-msg]").forEach(el => {
      guardClick(el as HTMLElement);
    });
  } catch (err) {}
})();

export {};
