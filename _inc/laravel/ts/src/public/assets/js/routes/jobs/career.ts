/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/career.js
 * @generated from original JavaScript - manual review recommended
 * @module career
 */

((): void => {
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const _Q = (s: string) => document.querySelector(s);
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const QA = (s: string) => Array.from(document.querySelectorAll(s)),
    DEFAULT_ROUTE_MSG =
      "Requested route is unavailable. Please contact technical support or your domain administrator.";
  const toast = (message: string): void => {
    const text = message || DEFAULT_ROUTE_MSG,
      hasBs = !!(
        document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
        window.bootstrap
      );
    let box = document.getElementById("toast-container");
    if (!box) {
      box = document.createElement("div");
      box.id = "toast-container";
      Object.assign(box.style, {
        position: "fixed",
        top: "1rem",
        right: "1rem",
        zIndex: "1060",
      });
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
  const bindLinkGuard = (a: Element): void => {
    if (!a || a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");
    a.addEventListener("click", (e: Event) => {
      const href = (a.getAttribute("href") ?? "#").trim(),
        url = (a.getAttribute("data-url") ?? href ?? "#").trim();
      if (url !== "#" && href !== "#") return;
      e.preventDefault();
      toast(a.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      a.setAttribute("data-failed-route", "true");
    });
  };
  const bindFormGuard = (f: Element): void => {
    if (!f || f.getAttribute("data-submit-guarded") === "true") return;
    f.setAttribute("data-submit-guarded", "true");
    f.addEventListener("submit", (e: Event) => {
      const action = (f.getAttribute("action") ?? "#").trim(),
        url = (f.getAttribute("data-url") ?? action ?? "#").trim();
      if (url !== "#" && action !== "#") return;
      e.preventDefault();
      toast(f.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      f.setAttribute("data-failed-route", "true");
    });
  };
  const initTooltips = (): void => {
    try {
      QA('[data-bs-toggle="tooltip"]').forEach((el: Element): void => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch (_) {
          console.error(`[career] Error:`, _);
        }
      });
    } catch (_) {
      console.error(`[career] Error:`, _);
    }
  };
  document.addEventListener("DOMContentLoaded", (): void => {
    QA("a[data-guard-msg], a[data-url]").forEach(bindLinkGuard);
    QA("form[data-guard-msg], form[data-url]").forEach(bindFormGuard);
    initTooltips();
  });
})();

export {};
