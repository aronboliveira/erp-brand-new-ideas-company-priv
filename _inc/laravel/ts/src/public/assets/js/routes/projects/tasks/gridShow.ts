/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/tasks/gridShow.js
 * @generated from original JavaScript - manual review recommended
 * @module gridShow
 */

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  function toast(msg: string | null): void {
    const m =
        msg ??
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
      hasBootstrap = typeof window.bootstrap.Toast !== "undefined";
    if (hasBootstrap) {
      let box = document.getElementById("toast-container");
      if (!box) {
        box = document.createElement("div");
        box.id = "toast-container";
        Object.assign(box.style, {
          position: "fixed",
          zIndex: "1080",
          right: "1rem",
          bottom: "1rem",
        });
        document.body.appendChild(box);
      }
      const t = document.createElement("div");
      t.className = "toast";
      for (const [k, v] of Object.entries({
        role: "alert",
        "aria-live": "assertive",
        "aria-atomic": "true",
      }))
        t.setAttribute(k, v);
      t.innerHTML = '<div class="toast-body"></div>';
      const tb = t.querySelector(".toast-body");
      if (tb) tb.textContent = m;
      box.appendChild(t);
      window.bootstrap.Toast.getOrCreateInstance(t, { delay: 3000 }).show();
    } else {
      alert(m);
    }
  }
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type

  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  function disabledUrl(a: Element) {
    const href = (a.getAttribute("href") ?? "").trim(),
      url = (a.getAttribute("data-url") || href || "").trim();
    if (!url || url === "#" || href === "#") return true;
    try {
      new URL(url, window.location.origin);
      return false;
    } catch (e) {
      return true;
    }
  }

  function guard(el: Element): void {
    const htmlEl = el as HTMLElement;
    if (!el || htmlEl.dataset.guardBound === "1") return;
    htmlEl.dataset.guardBound = "1";
    el.addEventListener("click", function (e: Event) {
      if (disabledUrl(el)) {
        e.preventDefault();
        toast(el.getAttribute("data-guard-msg"));
      }
    });
    if (!el.getAttribute("data-listener-bound-keydown")) {
      el.setAttribute("data-listener-bound-keydown", "1");
      htmlEl.addEventListener("keydown", function (e: KeyboardEvent) {
        if ((e.key === "Enter" || e.key === " ") && disabledUrl(el)) {
          e.preventDefault();
          toast(el.getAttribute("data-guard-msg"));
        }
      });
    }
  }

  function bind(): void {
    document.querySelectorAll("a.project-task-index-link").forEach(guard);
    document
      .querySelectorAll<HTMLElement>(".card-progress")
      .forEach(function (card) {
        if (card.dataset.cardBound === "1") return;
        card.dataset.cardBound = "1";
        card.addEventListener("click", function (e: Event) {
          const target = e.target as HTMLElement | null;
          if (
            target?.closest(
              'a,button,input,textarea,select,[role="button"],[data-ajax-popup]',
            )
          )
            return;
          const link = card.querySelector("a.project-task-index-link");
          if (!link) return;
          if (disabledUrl(link)) {
            e.preventDefault();
            toast(link.getAttribute("data-guard-msg"));
            return;
          }
          const url = (
            link.getAttribute("data-url") ??
            link.getAttribute("href") ??
            "#"
          ).trim();
          if (url && url !== "#") window.location.assign(url);
        });
      });
    if (
      window.bootstrap &&
      document.querySelector('[data-bs-toggle="tooltip"]')
    )
      // eslint-disable-next-line @typescript-eslint/no-unsafe-call
      [].slice
        .call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        .forEach(function (el: HTMLElement) {
          window.bootstrap.Tooltip.getOrCreateInstance(el);
        });
  }

  function observe(): void {
    if (!("MutationObserver" in window)) return;
    const mo = new MutationObserver(function (muts) {
      // eslint-disable-next-line @typescript-eslint/prefer-for-of
      for (let i = 0; i < muts.length; i++) {
        if (muts[i].addedNodes.length) {
          bind();
          break;
        }
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  }

  function init(): void {
    bind();
    observe();
  }

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", init)
    : init();
})();

export {};
