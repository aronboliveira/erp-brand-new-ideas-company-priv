/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/categories/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

((): void => {
  const F = {
    toast(message: string): void{
      const text =
        message ??
        "Requested route is unavailable. Please contact technical support or your domain administrator.";
      const hasBs = !!(
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
        b.textContent = text;
        t.appendChild(b);
        box.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(text);
      }
    },
    bindLinkGuard(a: HTMLElement | null): void{
      if (!a || a.getAttribute("data-listener-active") === "true") return;
      a.setAttribute("data-listener-active", "true");
      a.addEventListener("click", (e: Event) => {
        const href = (a.getAttribute("href") ?? "#").trim();
        const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
        if (url !== "#" && href !== "#") return;
        e.preventDefault();
        F.toast(a.getAttribute("data-guard-msg") ?? "");
        a.setAttribute("data-failed-route", "true");
      });
    },
    bindFormGuard(fm: HTMLElement | null): void{
      if (!fm || fm.getAttribute("data-submit-guarded") === "true") return;
      fm.setAttribute("data-submit-guarded", "true");
      fm.addEventListener("submit", (e: Event) => {
        const action = (fm.getAttribute("action") ?? "#").trim();
        const url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
        if (url !== "#" && action !== "#") return;
        e.preventDefault();
        F.toast(fm.getAttribute("data-guard-msg") ?? "");
        fm.setAttribute("data-failed-route", "true");
      });
    },
    initTooltips(): void{
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
    },
  };

  document.addEventListener("DOMContentLoaded", (): void => {
    document
      .querySelectorAll("a[data-guard-msg], a[data-url]")
      .forEach(el => F.bindLinkGuard(el as HTMLElement));
    document
      .querySelectorAll("form[data-guard-msg], form[data-url]")
      .forEach(el => F.bindFormGuard(el as HTMLElement));
    F.initTooltips();
  });
})();

export {};
