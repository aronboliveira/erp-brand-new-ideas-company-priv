/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applications/candidate.js
 * @generated from original JavaScript - manual review recommended
 * @module candidate
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const Q = s => document.querySelector(s);
  const QA = s => Array.from(document.querySelectorAll(s));
  const DEFAULT_ROUTE_MSG =
    "Requested route is unavailable. Please contact technical support or your domain administrator.";

  const toast = m => {
    const txt = m || DEFAULT_ROUTE_MSG;
    const hasBs = !!(
      document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      window.bootstrap
    );
    let box = Q("#toast-container");
    if (!box) {
      box = document.createElement("div");
      box.id = "toast-container";
      document.body.appendChild(box);
    }
    if (hasBs) {
      const t = document.createElement("div");
      t.className = "toast";
      t.setAttribute("role", "alert");
      t.setAttribute("aria-live", "assertive");
      t.setAttribute("aria-atomic", "true");
      const b = document.createElement("div");
      b.className = "toast-body";
      b.textContent = txt;
      t.appendChild(b);
      box.appendChild(t);
      bootstrap.Toast.getOrCreateInstance(t).show();
    } else {
      alert(txt);
    }
  };

  const bad = v => !v || v.trim() === "#" || /^javascript:/i.test(v.trim());

  const bindLinkGuard = el => {
    if (!el || el.dataset.listenerActive === "true") return;
    el.dataset.listenerActive = "true";
    el.addEventListener("click", e => {
      const href = el.getAttribute("href") ?? "#";
      const url = el.getAttribute("data-url") ?? href ?? "#";
      if (!bad(href) && !bad(url)) return;
      e.preventDefault();
      toast(el.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      el.dataset.failedRoute = "true";
    });
  };

  const bindFormGuard = fm => {
    if (!fm || fm.dataset.submitGuarded === "true") return;
    fm.dataset.submitGuarded = "true";
    fm.addEventListener("submit", e => {
      const action = fm.getAttribute("action") ?? "#";
      const url = fm.getAttribute("data-url") ?? action ?? "#";
      if (!bad(action) && !bad(url)) return;
      e.preventDefault();
      toast(fm.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      fm.dataset.failedRoute = "true";
    });
  };

  const initTooltips = (): void => {
    try {
      QA('[data-bs-toggle="tooltip"]').forEach((el: Element): void => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch (_) {}
      });
    } catch (_) {}
  };

  const initDataTables = (): void => {
    const tables = QA(".datatable");
    if (tables.length === 0) return;
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
    if (window.jQuery.fn && jQuery.fn.DataTable) {
      tables.forEach(t => jQuery(t).DataTable());
    }
  };

  const bindAll = root => {
    (root
      ? Array.from(root.querySelectorAll("a[data-guard-msg], a[data-url]"))
      : QA("a[data-guard-msg], a[data-url]")
    ).forEach(bindLinkGuard);
    (root
      ? Array.from(
          root.querySelectorAll("form[data-guard-msg], form[data-url]")
        )
      : QA("form[data-guard-msg], form[data-url]")
    ).forEach(bindFormGuard);
  };

  const observe = (): void => {
    if (!("MutationObserver" in window)) return;
    const mo = new MutationObserver(ms => {
      ms.forEach(m => {
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        m.addedNodes &&
          m.addedNodes.forEach(n => {
            if (!(n instanceof Element)) return;
            bindAll(n);
            // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
            if (n.matches && n.matches('[data-bs-toggle="tooltip"]')) {
              try {
                bootstrap.Tooltip.getOrCreateInstance(n);
              } catch (_) {}
            }
            // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
            n.querySelectorAll &&
              n.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el: Element): void => {
                try {
                  bootstrap.Tooltip.getOrCreateInstance(el);
                } catch (_) {}
              });
          });
      });
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  document.addEventListener("DOMContentLoaded", (): void => {
    bindAll();
    initTooltips();
    initDataTables();
    observe();
    QA(".job-app-show-link").forEach(bindLinkGuard);
  });
})();

export {};
