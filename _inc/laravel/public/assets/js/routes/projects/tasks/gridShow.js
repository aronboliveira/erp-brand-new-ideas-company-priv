/**
 * @file Project Tasks Grid Show Guard
 * @description Guards project task links and card clicks using ERPGuard singleton
 * @requires ERPGuard
 */
(() => {
  const guard = window.ERPGuard;

  if (!guard) {
    
    return;
  }

  const FALLBACK = "Requested route is unavailable. Please contact technical support or your domain administrator.";

  /**
   * Guard a link element
   * @param {HTMLElement} el - Element to guard
   */
  const guardEl = el => {
    if (!el || el.dataset.guardBound === "1") return;
    el.dataset.guardBound = "1";

    const handler = e => {
      const href = el.getAttribute("href") || "#";
      const url = el.getAttribute("data-url") || href;

      if (guard.isInvalidUrl(href) && guard.isInvalidUrl(url)) {
        e.preventDefault();
        guard.showToast(el.getAttribute("data-guard-msg") || FALLBACK, "error");
      }
    };

    el.addEventListener("click", handler);
    el.addEventListener("keydown", e => {
      if (e.key === "Enter" || e.key === " ") handler(e);
    });
  };

  /**
   * Bind guards to elements
   */
  const bind = () => {
    // Guard task links
    document.querySelectorAll("a.project-task-index-link").forEach(guardEl);

    // Guard card clicks
    document.querySelectorAll(".card-progress").forEach(card => {
      if (card.dataset.cardBound === "1") return;
      card.dataset.cardBound = "1";

      card.addEventListener("click", e => {
        if (e.target.closest('a,button,input,textarea,select,[role="button"],[data-ajax-popup]')) return;

        const link = card.querySelector("a.project-task-index-link");
        if (!link) return;

        const href = link.getAttribute("href") || "#";
        const url = link.getAttribute("data-url") || href;

        if (guard.isInvalidUrl(href) && guard.isInvalidUrl(url)) {
          e.preventDefault();
          guard.showToast(link.getAttribute("data-guard-msg") || FALLBACK, "error");
          return;
        }

        if (!guard.isInvalidUrl(url)) window.location.assign(url);
      });
    });

    // Initialize tooltips
    if (window.bootstrap?.Tooltip) {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        window.bootstrap.Tooltip.getOrCreateInstance(el);
      });
    }
  };

  const observe = () => {
    const mo = new MutationObserver(muts => {
      for (const m of muts) {
        if (m.addedNodes?.length) {
          bind();
          break;
        }
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  const init = () => {
    bind();
    observe();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
