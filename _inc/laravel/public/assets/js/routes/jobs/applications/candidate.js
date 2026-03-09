/**
 * @file Job Applications Candidate Route Guard
 * @description Guards candidate links/forms, initializes DataTables and tooltips using ERPGuard singleton with MutationObserver
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }

    const Q = s => document.querySelector(s);
    const QA = s => Array.from(document.querySelectorAll(s));

    const initDataTables = () => {
      const tables = QA(".datatable");
      if (!tables.length) return;
      if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
        tables.forEach(t => jQuery(t).DataTable());
      }
    };

    const bindAll = root => {
      const links = root
        ? Array.from(root.querySelectorAll("a[data-guard-msg], a[data-url]"))
        : QA("a[data-guard-msg], a[data-url]");
      const forms = root
        ? Array.from(
            root.querySelectorAll("form[data-guard-msg], form[data-url]"),
          )
        : QA("form[data-guard-msg], form[data-url]");

      links.forEach(link => guard.bindClickGuard(link));
      forms.forEach(form => guard.bindSubmitGuard(form));
    };

    const observe = () => {
      if (!("MutationObserver" in window)) return;
      const mo = new MutationObserver(ms => {
        ms.forEach(m => {
          m.addedNodes &&
            m.addedNodes.forEach(n => {
              if (!(n instanceof Element)) return;
              bindAll(n);
              if (n.matches && n.matches('[data-bs-toggle="tooltip"]')) {
                try {
                  window.bootstrap?.Tooltip.getOrCreateInstance(n);
                } catch (_) {}
              }
              n.querySelectorAll &&
                n.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                  try {
                    window.bootstrap?.Tooltip.getOrCreateInstance(el);
                  } catch (_) {}
                });
            });
        });
      });
      mo.observe(document.body, { childList: true, subtree: true });
    };

    document.addEventListener("DOMContentLoaded", () => {
      bindAll();
      guard.initTooltips();
      initDataTables();
      observe();
      guard.bindClickGuard(".job-app-show-link");
    });
  } catch (err) {
    console.error("Error initializing jobs/applications/candidate guard:", err);
  }
})();
