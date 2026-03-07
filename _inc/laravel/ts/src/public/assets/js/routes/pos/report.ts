/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/report.js
 * @generated from original JavaScript - manual review recommended
 * @module report
 */

/* global bootstrap, $, jQuery */
((): void => {
  const $ = window.jQuery;
  if (!$) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery not found for anchors.js");
    } catch (_) {}
    return;
  }
  const ERR_FB = "# ERROR";
  const DCL = "data-client-localized";
  const DGM = "data-guard-msg";
  const DSL = "data-sv-localized";
  const DLA = "data-listener-active";
  const MSG_KEY = "pos_route_unavailable";
  const hasBootstrapCss = () =>
    !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');
  const getMsg = (el: HTMLElement | null) => {
    let msg = ERR_FB;
    if (!el) return msg;
    if (el.getAttribute(DSL) === "true" || el.getAttribute(DCL) === "true") {
      msg = el.getAttribute(DGM) || ERR_FB;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[MSG_KEY] ||
        el.getAttribute(DGM) ||
        window.translations?.en?.[MSG_KEY] ||
        ERR_FB;
      if (msg !== ERR_FB) {
        el.setAttribute(DGM, msg);
        el.setAttribute(DCL, "true");
      }
    }
    return msg;
  };
  const showError = (el: HTMLElement | null) => {
    const msg = getMsg(el);
    if (hasBootstrapCss() && window.bootstrap) {
      let wrap = document.getElementById("toast-wrap-pos-guard");
      if (!wrap) {
        wrap = document.createElement("div");
        wrap.id = "toast-wrap-pos-guard";
        wrap.className = "position-fixed top-0 end-0 p-3";
        wrap.style.zIndex = "1080";
        document.body.appendChild(wrap);
      }
      const t = document.createElement("div");
      t.className = "toast align-items-center text-bg-danger border-0";
      t.setAttribute("role", "alert");
      t.setAttribute("aria-live", "assertive");
      t.setAttribute("aria-atomic", "true");
      t.innerHTML =
        '<div class="d-flex"><div class="toast-body">' +
        msg +
        '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
      wrap.appendChild(t);
      new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
    } else {
      alert(msg);
    }
  };
  const handlers = new WeakMap();
  const onClick = (el: HTMLElement) => (e: Event) => {
    try {
      const url = el.getAttribute("data-url");
      const href = el.getAttribute("href");
      if ((!url || url === "#") && (!href || href === "#")) {
        e.preventDefault();
        showError(el);
      }
    } catch (_) {}
  };
  const bind = (el: HTMLElement | null) => {
    if (!el || el.getAttribute(DLA) === "true") return;
    el.setAttribute(DLA, "true");
    const h = onClick(el);
    handlers.set(el, h);
    $(el as Element).on("click", h);
  };
  const unbind = (el: HTMLElement | null) => {
    if (!el) return;
    const h = handlers.get(el);
    if (h) {
      $(el as Element).off("click", h);
      handlers.delete(el);
    }
    el.removeAttribute(DLA);
  };
  const scan = (root?: Element | Document) => {
    const r = root || document;
    const list = r.querySelectorAll<HTMLElement>(
      "a[" + DGM + "]:not([" + DLA + '="true"])',
    );
    list.forEach(bind);
  };
  const ready = (): void => {
    try {
      scan(document);
    } catch (_) {}
  };
  if (document.readyState === "loading") {
    $(ready);
  } else {
    ready();
  }
  const mo = new MutationObserver(muts => {
    muts.forEach(m => {
      m.addedNodes &&
        m.addedNodes.forEach(n => {
          if (n.nodeType === 1) scan(n as Element);
        });
      m.removedNodes &&
        m.removedNodes.forEach(n => {
          if (n.nodeType === 1) {
            const el = n as HTMLElement;
            if (el.hasAttribute?.(DLA)) unbind(el);
            el.querySelectorAll?.<HTMLElement>("a[" + DLA + "]").forEach(
              unbind,
            );
          }
        });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });
  ((): void => {
    const $ = window.jQuery;
    if (!$) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery not found for summary.js");
      } catch (_) {}
      return;
    }
    const init = (): void => {
      try {
        if (!$.fn.DataTable) return;
        const $t = $(".datatable").filter(
          (i: number, el: HTMLElement) =>
            el.getAttribute("data-dt-init") !== "true",
        );
        if (!$t.length) return;
        $t.each(function (): void {
          $(this).attr("data-dt-init", "true").DataTable({ order: [] });
        });
      } catch (_) {}
    };
    if (document.readyState === "loading") {
      $(init);
    } else {
      init();
    }
  })();
})();

export {};
