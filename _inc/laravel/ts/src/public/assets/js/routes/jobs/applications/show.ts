/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applications/show.js
 * @generated from original JavaScript - manual review recommended
 * @module show
 */

((): void => {
  const D = (m: unknown): void => {
      const t = String(
          m ??
            "Requested route is unavailable. Please contact technical support or your domain administrator.",
        ),
        hasBs = !!(
          document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
          window.bootstrap
        );
      let box = document.getElementById("toast-container");
      if (!box) {
        box = document.createElement("div");
        box.id = "toast-container";
        document.body.appendChild(box);
      }
      if (hasBs) {
        const el = document.createElement("div");
        el.className = "toast";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          el.setAttribute(k, v);
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = t;
        el.appendChild(b);
        box.appendChild(el);
        bootstrap.Toast.getOrCreateInstance(el).show();
      } else {
        alert(t);
      }
    },
    bindLink = (a: HTMLElement | null): void => {
      if (!a || a.getAttribute("data-listener-active") === "true") return;
      a.setAttribute("data-listener-active", "true");
      a.addEventListener("click", (e: Event) => {
        const href = (a.getAttribute("href") ?? "#").trim(),
          url = (a.getAttribute("data-url") ?? href ?? "#").trim();
        if (url !== "#" && href !== "#") return;
        e.preventDefault();
        D(a.getAttribute("data-guard-msg") ?? "");
      });
    },
    bindForm = (f: HTMLElement | null): void => {
      if (!f || f.getAttribute("data-submit-guarded") === "true") return;
      f.setAttribute("data-submit-guarded", "true");
      f.addEventListener("submit", (e: Event) => {
        const action = (f.getAttribute("action") ?? "#").trim(),
          url = (f.getAttribute("data-url") ?? action ?? "#").trim();
        if (url !== "#" && action !== "#") return;
        e.preventDefault();
        D(f.getAttribute("data-guard-msg") ?? "");
      });
    },
    tips = (): void => {
      try {
        document
          .querySelectorAll('[data-bs-toggle="tooltip"]')
          .forEach((el: Element): void => {
            try {
              bootstrap.Tooltip.getOrCreateInstance(el);
            } catch (_) {
              console.error(`[show] Error:`, _);
            }
          });
      } catch (_) {
        console.error(`[show] Error:`, _);
      }
    };
  document.addEventListener("DOMContentLoaded", (): void => {
    document
      .querySelectorAll("a[data-guard-msg],a[data-url]")
      .forEach(el => bindLink(el as HTMLElement));
    document
      .querySelectorAll("form[data-guard-msg],form[data-url]")
      .forEach(el => bindForm(el as HTMLElement));
    tips();
  });
})();

export {};
